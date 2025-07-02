<?php

namespace App\Http\Controllers;

use App\Models\LetterNumber;
use App\Models\LetterSubject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

abstract class BaseDocumentController extends Controller
{
    /**
     * Get document type identifier
     * Must be implemented by child classes
     *
     * @return string
     */
    abstract protected function getDocumentType(): string;

    /**
     * Get default letter category for this document type
     * Must be implemented by child classes
     *
     * @return string
     */
    abstract protected function getDefaultCategory(): string;

    /**
     * Get model class name for this document
     * Must be implemented by child classes
     *
     * @return string
     */
    abstract protected function getModelClass(): string;

    /**
     * Handle letter number integration saat create/update document
     *
     * @param Request $request
     * @param \Illuminate\Database\Eloquent\Model $document
     * @return int|null
     */
    protected function handleLetterNumberIntegration(Request $request, $document)
    {
        $letterNumberId = null;

        try {
            // Check if we should auto-assign based on subject configuration
            $autoAssignSubject = $this->getAutoAssignSubject();
            if ($autoAssignSubject && $autoAssignSubject->hasDocumentIntegration()) {
                $letterNumberId = $this->autoAssignFromSubject($autoAssignSubject, $document, $request);
            } elseif ($request->number_option === 'existing' && $request->letter_number_id) {
                // Use existing reserved number
                $letterNumberId = $request->letter_number_id;

                // Validate letter number exists and is reserved
                $letterNumber = LetterNumber::find($letterNumberId);
                if (!$letterNumber || $letterNumber->status !== 'reserved') {
                    throw new \Exception('Selected letter number is not available');
                }
            } elseif ($request->number_option === 'new') {
                // Create new letter number
                $letterNumber = $this->createNewLetterNumber($request);
                $letterNumberId = $letterNumber->id;
            }

            // Assign letter number to document if available
            if ($letterNumberId && method_exists($document, 'assignLetterNumber')) {
                $success = $document->assignLetterNumber($letterNumberId);
                if (!$success) {
                    throw new \Exception('Failed to assign letter number to document');
                }
            }

            return $letterNumberId;
        } catch (\Exception $e) {
            Log::error('Letter number integration failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get auto-assign subject for this document type
     *
     * @return LetterSubject|null
     */
    protected function getAutoAssignSubject()
    {
        $documentType = $this->getDocumentType();
        $categoryCode = $this->getDefaultCategory();

        return LetterSubject::where('document_model', ucfirst($documentType))
            ->where('category_code', $categoryCode)
            ->where('is_active', true)
            ->ordered()
            ->first();
    }

    /**
     * Auto-assign letter number from subject configuration
     *
     * @param LetterSubject $subject
     * @param \Illuminate\Database\Eloquent\Model $document
     * @param Request $request
     * @return int|null
     */
    protected function autoAssignFromSubject($subject, $document, $request)
    {
        try {
            $letterNumberData = [
                'category_code' => $subject->category_code,
                'subject_id' => $subject->id,
                'custom_subject' => $subject->subject_name,
                'administration_id' => $this->getAdministrationId($request),
                'project_id' => $this->getProjectId($request),
                'purpose' => $this->getPurpose($request),
                'destination' => $this->getDestination($request),
                'letter_date' => $this->getLetterDate($request),
                'start_date' => $this->getStartDate($request),
                'end_date' => $this->getEndDate($request),
                'remarks' => $this->getRemarks($request),
                'user_id' => auth()->id(),
                'reserved_by' => auth()->id(),
            ];

            $letterNumber = LetterNumber::create($letterNumberData);

            if ($letterNumber) {
                return $letterNumber->id;
            }

            throw new \Exception('Failed to create letter number from subject');
        } catch (\Exception $e) {
            Log::error('Auto-assign letter number failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get available subjects for this document type
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    protected function getAvailableSubjects()
    {
        $documentType = $this->getDocumentType();
        $categoryCode = $this->getDefaultCategory();

        return LetterSubject::active()
            ->where('document_model', ucfirst($documentType))
            ->where('category_code', $categoryCode)
            ->ordered()
            ->get();
    }

    /**
     * Create new letter number untuk document
     *
     * @param Request $request
     * @return LetterNumber
     */
    protected function createNewLetterNumber(Request $request)
    {
        return LetterNumber::create([
            'category_code' => $request->letter_category ?? $this->getDefaultCategory(),
            'subject_id' => $this->getOrCreateSubject(
                $request->letter_subject,
                $request->letter_category ?? $this->getDefaultCategory()
            ),
            'administration_id' => $this->getAdministrationId($request),
            'project_id' => $this->getProjectId($request),
            'purpose' => $this->getPurpose($request),
            'destination' => $this->getDestination($request),
            'letter_date' => $this->getLetterDate($request),
            'start_date' => $this->getStartDate($request),
            'end_date' => $this->getEndDate($request),
            'custom_subject' => $request->letter_subject,
            'remarks' => $this->getRemarks($request),
            'user_id' => auth()->id(),
        ]);
    }

    /**
     * Get or create letter subject
     *
     * @param string|null $subjectName
     * @param string $categoryCode
     * @return int|null
     */
    protected function getOrCreateSubject($subjectName, $categoryCode)
    {
        if (!$subjectName) {
            return null;
        }

        $subject = LetterSubject::where('category_code', $categoryCode)
            ->where('subject_name', $subjectName)
            ->first();

        if (!$subject) {
            $subject = LetterSubject::create([
                'subject_name' => $subjectName,
                'category_code' => $categoryCode,
                'user_id' => auth()->id(),
            ]);
        }

        return $subject->id;
    }

    /**
     * Load available letter numbers untuk create form
     *
     * @param string|null $categoryCode
     * @return \Illuminate\Database\Eloquent\Collection
     */
    protected function loadAvailableLetterNumbers($categoryCode = null)
    {
        $categoryCode = $categoryCode ?? $this->getDefaultCategory();

        return LetterNumber::byCategory($categoryCode)
            ->reserved()
            ->active()
            ->with(['subject', 'administration.employee'])
            ->orderBy('sequence_number', 'desc')
            ->limit(20)
            ->get();
    }

    /**
     * Get validation rules untuk letter number integration
     *
     * @return array
     */
    protected function getLetterNumberValidationRules()
    {
        return [
            'number_option' => 'nullable|in:existing,new',
            'letter_number_id' => 'nullable|exists:letter_numbers,id',
            'letter_category' => 'nullable|exists:letter_categories,category_code',
            'letter_subject' => 'nullable|string|max:200',
        ];
    }

    // Abstract methods yang harus diimplementasi oleh child classes untuk letter number creation

    /**
     * Get administration ID dari request untuk letter number
     *
     * @param Request $request
     * @return int|null
     */
    abstract protected function getAdministrationId(Request $request);

    /**
     * Get project ID dari request untuk letter number
     *
     * @param Request $request
     * @return int|null
     */
    protected function getProjectId(Request $request)
    {
        return null; // Default implementation, can be overridden
    }

    /**
     * Get purpose dari request untuk letter number
     *
     * @param Request $request
     * @return string
     */
    abstract protected function getPurpose(Request $request);

    /**
     * Get destination dari request untuk letter number
     *
     * @param Request $request
     * @return string|null
     */
    protected function getDestination(Request $request)
    {
        return null; // Default implementation, can be overridden
    }

    /**
     * Get letter date dari request untuk letter number
     *
     * @param Request $request
     * @return string
     */
    abstract protected function getLetterDate(Request $request);

    /**
     * Get start date dari request untuk letter number
     *
     * @param Request $request
     * @return string|null
     */
    protected function getStartDate(Request $request)
    {
        return null; // Default implementation, can be overridden
    }

    /**
     * Get end date dari request untuk letter number
     *
     * @param Request $request
     * @return string|null
     */
    protected function getEndDate(Request $request)
    {
        return null; // Default implementation, can be overridden
    }

    /**
     * Get remarks dari request untuk letter number
     *
     * @param Request $request
     * @return string
     */
    protected function getRemarks(Request $request)
    {
        return "Auto-generated for {$this->getDocumentType()}";
    }

    /**
     * Helper method untuk response success dengan SweetAlert
     *
     * @param string $message
     * @param string $route
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function successResponse($message, $route)
    {
        return redirect()->route($route)
            ->with('toast_success', $message);
    }

    /**
     * Helper method untuk response error dengan SweetAlert
     *
     * @param string $message
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function errorResponse($message)
    {
        return redirect()->back()
            ->with('toast_error', $message)
            ->withInput();
    }
}
