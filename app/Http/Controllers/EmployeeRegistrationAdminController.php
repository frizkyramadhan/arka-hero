<?php

namespace App\Http\Controllers;

use App\Models\EmployeeRegistration;
use App\Models\EmployeeRegistrationToken;
use App\Services\EmployeeRegistrationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class EmployeeRegistrationAdminController extends Controller
{
    protected $registrationService;

    public function __construct(EmployeeRegistrationService $registrationService)
    {
        $this->middleware('auth');
        $this->middleware('role_or_permission:employees.create')->only(['invite', 'bulkInvite']);
        $this->middleware('role_or_permission:employees.edit')->only(['approve', 'reject']);
        $this->registrationService = $registrationService;
    }

    /**
     * Show pending registrations
     */
    public function index()
    {
        $title = 'Employee Registrations';
        $subtitle = 'Manage Employee Registration Requests';

        return view('employee.registration.admin.index', compact('title', 'subtitle'));
    }

    /**
     * Get pending registrations data for DataTable
     */
    public function getPendingRegistrations(Request $request)
    {
        $registrations = EmployeeRegistration::with(['token'])
            ->where('status', 'submitted')
            ->orderBy('created_at', 'desc');

        return datatables()->of($registrations)
            ->addIndexColumn()
            ->addColumn('email', function ($registration) {
                return $registration->token->email;
            })
            ->addColumn('fullname', function ($registration) {
                return $registration->personal_data['fullname'] ?? 'N/A';
            })
            ->addColumn('identity_card', function ($registration) {
                return $registration->personal_data['identity_card'] ?? 'N/A';
            })
            ->addColumn('submitted_at', function ($registration) {
                return $registration->created_at->format('d-M-Y H:i');
            })
            ->addColumn('documents_count', function ($registration) {
                return $registration->documents()->count();
            })
            ->addColumn('action', function ($registration) {
                return view('employee.registration.admin.action', compact('registration'))->render();
            })
            ->rawColumns(['action'])
            ->toJson();
    }

    /**
     * Show registration details
     */
    public function show($id)
    {
        $registration = EmployeeRegistration::with(['token', 'documents'])->findOrFail($id);

        $title = 'Registration Details';
        $subtitle = 'Review Employee Registration';

        return view('employee.registration.admin.show', compact('registration', 'title', 'subtitle'));
    }

    /**
     * Show invite form
     */
    public function showInviteForm()
    {
        $title = 'Invite Employee';
        $subtitle = 'Send Registration Invitation';

        return view('employee.registration.admin.invite', compact('title', 'subtitle'));
    }

    /**
     * Send registration invitation
     */
    public function invite(Request $request)
    {
        $request->validate([
            'email' => 'required|email|unique:employee_registration_tokens,email',
        ]);

        try {
            $result = $this->registrationService->inviteEmployee(
                $request->email,
                Auth::id()
            );

            if ($result['success']) {
                return redirect()->back()->with('toast_success', $result['message']);
            }

            return redirect()->back()->with('toast_error', $result['message']);
        } catch (\Exception $e) {
            return redirect()->back()->with('toast_error', 'Failed to send invitation: ' . $e->getMessage());
        }
    }

    /**
     * Bulk invite employees
     */
    public function bulkInvite(Request $request)
    {
        $request->validate([
            'emails' => 'required|string',
        ]);

        try {
            // Parse emails from textarea
            $emails = array_filter(
                array_map('trim', explode("\n", $request->emails)),
                function ($email) {
                    return filter_var($email, FILTER_VALIDATE_EMAIL);
                }
            );

            if (empty($emails)) {
                return redirect()->back()->with('toast_error', 'No valid email addresses found');
            }

            $results = $this->registrationService->bulkInviteEmployees($emails, Auth::id());

            $message = "Invitations processed: {$results['summary']['successful']} successful, {$results['summary']['failed']} failed";

            return redirect()->back()->with('toast_success', $message);
        } catch (\Exception $e) {
            return redirect()->back()->with('toast_error', 'Failed to process bulk invitations: ' . $e->getMessage());
        }
    }

    /**
     * Approve registration
     */
    public function approve(Request $request, $id)
    {
        $request->validate([
            'admin_notes' => 'nullable|string|max:1000'
        ]);

        try {
            $registration = EmployeeRegistration::findOrFail($id);

            DB::beginTransaction();

            $registration->update([
                'status' => 'approved',
                'admin_notes' => $request->admin_notes,
                'reviewed_by' => Auth::id(),
                'reviewed_at' => now()
            ]);

            // Integrate with approval system if available
            if ($registration->approval) {
                // Process approval action through approval system
                $approvalEngine = app(\App\Services\ApprovalEngineService::class);
                $approvalEngine->processApproval(
                    $registration->approval->id,
                    Auth::id(),
                    'approve',
                    $request->admin_notes
                );
            }

            // TODO: Create actual employee record from registration data
            // $this->createEmployeeFromRegistration($registration);

            DB::commit();
            return redirect()->back()->with('toast_success', 'Registration approved successfully');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('toast_error', 'Failed to approve registration: ' . $e->getMessage());
        }
    }

    /**
     * Reject registration
     */
    public function reject(Request $request, $id)
    {
        $request->validate([
            'admin_notes' => 'required|string|max:1000'
        ]);

        try {
            $registration = EmployeeRegistration::findOrFail($id);

            DB::beginTransaction();

            $registration->update([
                'status' => 'rejected',
                'admin_notes' => $request->admin_notes,
                'reviewed_by' => Auth::id(),
                'reviewed_at' => now()
            ]);

            // Integrate with approval system if available
            if ($registration->approval) {
                // Process rejection action through approval system
                $approvalEngine = app(\App\Services\ApprovalEngineService::class);
                $approvalEngine->processApproval(
                    $registration->approval->id,
                    Auth::id(),
                    'reject',
                    $request->admin_notes
                );
            }

            DB::commit();

            return redirect()->back()->with('toast_success', 'Registration rejected');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('toast_error', 'Failed to reject registration: ' . $e->getMessage());
        }
    }

    /**
     * Download document
     */
    public function downloadDocument($registrationId, $documentId)
    {
        try {
            $registration = EmployeeRegistration::findOrFail($registrationId);
            $document = $registration->documents()->findOrFail($documentId);

            if (!Storage::disk('private')->exists($document->file_path)) {
                return redirect()->back()->with('toast_error', 'File not found');
            }

            return response()->download(storage_path('app/private/' . $document->file_path), $document->original_filename);
        } catch (\Exception $e) {
            return redirect()->back()->with('toast_error', 'Failed to download document: ' . $e->getMessage());
        }
    }

    /**
     * Get tokens data for DataTable
     */
    public function getTokens()
    {
        $tokens = EmployeeRegistrationToken::orderBy('created_at', 'desc');

        return datatables()->of($tokens)
            ->addIndexColumn()
            ->addColumn('status', function ($token) {
                if ($token->expires_at < now()) {
                    return 'expired';
                } elseif ($token->used_at) {
                    return 'used';
                } else {
                    return 'active';
                }
            })
            ->addColumn('action', function ($token) {
                $status = $token->expires_at < now() ? 'expired' : ($token->status === 'used' ? 'used' : 'active');
                $actions = '';

                if ($status === 'active') {
                    $actions .= '<button class="btn btn-sm btn-warning mr-1" onclick="resendInvitation(\'' . $token->id . '\')" title="Resend Invitation">
                                    <i class="fas fa-paper-plane"></i>
                                </button>';
                }

                // Allow deletion for all tokens except used ones
                if ($status !== 'used') {
                    $actions .= '<button class="btn btn-sm btn-danger" onclick="deleteToken(\'' . $token->id . '\')" title="Delete Token">
                                    <i class="fas fa-trash"></i>
                                </button>';
                }

                return $actions;
            })
            ->rawColumns(['action'])
            ->toJson();
    }

    /**
     * Get registration statistics
     */
    public function getStats()
    {
        $stats = $this->registrationService->getRegistrationStats();

        return response()->json($stats);
    }

    /**
     * Clean up expired tokens
     */
    public function cleanupExpiredTokens(Request $request)
    {
        try {
            $cleaned = $this->registrationService->cleanupExpiredTokens();

            $message = "Cleaned up {$cleaned} expired tokens";

            return redirect()->back()->with('toast_success', $message);
        } catch (\Exception $e) {
            return redirect()->back()->with('toast_error', 'Failed to cleanup expired tokens: ' . $e->getMessage());
        }
    }

    /**
     * Resend invitation email
     */
    public function resendInvitation(Request $request, $tokenId)
    {
        try {
            $token = EmployeeRegistrationToken::findOrFail($tokenId);

            // Check if token is still valid for resending
            if ($token->status !== 'pending') {
                return redirect()->back()->with('toast_error', 'Cannot resend invitation for this token status: ' . $token->status);
            }

            if ($token->expires_at < now()) {
                return redirect()->back()->with('toast_error', 'Cannot resend invitation. Token has expired.');
            }

            $result = $this->registrationService->resendInvitation($token);

            if ($result['success']) {
                return redirect()->back()->with('toast_success', $result['message']);
            }

            return redirect()->back()->with('toast_error', $result['message']);
        } catch (\Exception $e) {
            return redirect()->back()->with('toast_error', 'Failed to resend invitation: ' . $e->getMessage());
        }
    }

    /**
     * Delete token
     */
    public function deleteToken(Request $request, $tokenId)
    {
        try {
            $token = EmployeeRegistrationToken::findOrFail($tokenId);

            // Check if token can be deleted
            if ($token->status === 'used') {
                return redirect()->back()->with('toast_error', 'Cannot delete used token');
            }

            $email = $token->email;
            $token->delete();

            return redirect()->back()->with('toast_success', "Token for {$email} has been deleted");
        } catch (\Exception $e) {
            return redirect()->back()->with('toast_error', 'Failed to delete token: ' . $e->getMessage());
        }
    }

    /**
     * Create employee from approved registration
     */
    private function createEmployeeFromRegistration(EmployeeRegistration $registration)
    {
        // TODO: Implement logic to create actual employee record
        // This would involve creating records in employees, administrations,
        // and other related tables based on the registration data
    }
}
