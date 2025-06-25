<?php

namespace App\Http\Controllers;

use App\Models\Bank;
use App\Models\Religion;
use App\Models\Position;
use App\Models\Project;
use App\Models\Employee;
use App\Models\EmployeeRegistrationToken;
use App\Models\EmployeeRegistration;
use App\Models\RegistrationDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class EmployeeRegistrationController extends Controller
{
    /**
     * Show the employee registration form
     */
    public function showForm($token)
    {
        // Validate token
        $tokenRecord = EmployeeRegistrationToken::where('token', $token)
            ->where('status', 'pending')
            ->where('expires_at', '>', now())
            ->first();

        if (!$tokenRecord) {
            return view('employee.registration.expired')->with([
                'title' => 'Registration Link Expired',
                'message' => 'This registration link has expired or is invalid. Please contact HR for a new link.'
            ]);
        }

        // Get existing registration data if any
        $registration = EmployeeRegistration::where('token_id', $tokenRecord->id)->first();

        // Get reference data
        $religions = Religion::orderBy('religion_name', 'asc')->get();
        $banks = Bank::orderBy('bank_name', 'asc')->get();
        $positions = Position::with('department')->orderBy('position_name', 'asc')->get();
        $projects = Project::orderBy('project_code', 'asc')->get();

        return view('employee.registration.form', compact(
            'token',
            'tokenRecord',
            'registration',
            'religions',
            'banks',
            'positions',
            'projects'
        ))->with([
            'title' => 'Employee Self Registration',
            'subtitle' => 'Complete Your Employee Information'
        ]);
    }

    /**
     * Store employee registration data
     */
    public function store(Request $request, $token)
    {
        // Validate token
        $tokenRecord = $this->validateToken($token);
        if (!$tokenRecord) {
            if ($request->ajax()) {
                return response()->json(['error' => 'Invalid or expired token'], 403);
            }
            return redirect()->route('employee.registration.expired');
        }

        // Validate request
        $validator = $this->validateEmployeeData($request);
        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            // Determine if this is a submit action
            $isSubmit = $request->has('submit') && $request->submit == 'true';
            $status = $isSubmit ? 'submitted' : 'draft';

            // Save or update registration
            $registration = EmployeeRegistration::updateOrCreate(
                ['token_id' => $tokenRecord->id],
                [
                    'personal_data' => $request->all(),
                    'status' => $status
                ]
            );

            // If submitting, mark token as used
            if ($isSubmit) {
                $tokenRecord->update(['status' => 'used']);

                // Send notification to HR (implement if needed)
                // $this->notifyHR($registration);

                // Redirect to success page
                return redirect()->route('employee.registration.success', $token);
            }

            // For AJAX save requests
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Data saved successfully',
                    'registration_id' => $registration->id
                ]);
            }

            // For regular form submissions (save only)
            return redirect()->back()->with('success', 'Data saved successfully');
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json(['error' => 'Failed to save data'], 500);
            }
            return redirect()->back()->with('error', 'Failed to save data')->withInput();
        }
    }

    /**
     * Upload documents
     */
    public function uploadDocument(Request $request, $token)
    {
        $tokenRecord = $this->validateToken($token);
        if (!$tokenRecord) {
            return response()->json(['error' => 'Invalid or expired token'], 403);
        }

        $request->validate([
            'document_type' => 'required|string',
            'file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120' // 5MB max
        ]);

        $registration = EmployeeRegistration::where('token_id', $tokenRecord->id)->first();
        if (!$registration) {
            return response()->json(['error' => 'Registration not found'], 404);
        }

        try {
            // Secure file upload
            $file = $request->file('file');
            $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
            $path = "registration-documents/{$registration->id}";

            // Store file securely
            $filePath = $file->storeAs($path, $filename, 'private');

            // Save document record
            RegistrationDocument::create([
                'registration_id' => $registration->id,
                'document_type' => $request->document_type,
                'original_filename' => $file->getClientOriginalName(),
                'stored_filename' => $filename,
                'file_path' => $filePath,
                'file_size' => $file->getSize(),
                'mime_type' => $file->getMimeType()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Document uploaded successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to upload document'], 500);
        }
    }

    /**
     * Show success page
     */
    public function success($token)
    {
        // Validate that token exists and was used
        $tokenRecord = EmployeeRegistrationToken::where('token', $token)
            ->where('status', 'used')
            ->first();

        if (!$tokenRecord) {
            return redirect()->route('employee.registration.expired');
        }

        $registration = EmployeeRegistration::where('token_id', $tokenRecord->id)
            ->where('status', 'submitted')
            ->first();

        if (!$registration) {
            return redirect()->route('employee.registration.expired');
        }

        return view('employee.registration.success', compact('registration'))->with([
            'title' => 'Registration Submitted Successfully',
            'subtitle' => 'Thank you for completing your registration'
        ]);
    }

    /**
     * Show expired token page
     */
    public function expired()
    {
        return view('employee.registration.expired')->with([
            'title' => 'Registration Link Expired',
            'subtitle' => 'This registration link has expired or is invalid'
        ]);
    }

    /**
     * Validate token
     */
    private function validateToken($token)
    {
        return EmployeeRegistrationToken::where('token', $token)
            ->where('status', 'pending')
            ->where('expires_at', '>', now())
            ->first();
    }

    /**
     * Validate employee data
     */
    private function validateEmployeeData(Request $request)
    {
        return Validator::make($request->all(), [
            'fullname' => 'required|string|max:255',
            'identity_card' => 'required|string|max:16|unique:employees,identity_card',
            'emp_pob' => 'required|string|max:255',
            'emp_dob' => 'required|date',
            'religion_id' => 'required|exists:religions,id',
            'gender' => 'required|in:male,female',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'address' => 'required|string',
            // Add other validation rules as needed
        ]);
    }
}
