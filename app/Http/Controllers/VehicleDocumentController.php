<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use App\Models\VehicleDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class VehicleDocumentController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:vehicle-documents.show')->only(['download']);
        $this->middleware('permission:vehicle-documents.create')->only(['store']);
        $this->middleware('permission:vehicle-documents.edit')->only(['update']);
        $this->middleware('permission:vehicle-documents.delete')->only(['destroy']);
    }

    public function store(Request $request, Vehicle $vehicle)
    {
        $data = $this->validated($request);

        try {
            DB::beginTransaction();

            $status = $data['status'] ?? 'active';
            if (($status !== 'archived') && ! empty($data['expiry_date'])
                && \Carbon\Carbon::parse($data['expiry_date'])->lt(now()->startOfDay())) {
                $status = 'expired';
            }

            $document = VehicleDocument::create([
                'vehicle_id' => $vehicle->id,
                'document_type' => $data['document_type'],
                'document_number' => $data['document_number'] ?? null,
                'document_name' => $data['document_name'],
                'issue_date' => $data['issue_date'] ?? null,
                'expiry_date' => $data['expiry_date'] ?? null,
                'issuing_authority' => $data['issuing_authority'] ?? null,
                'notes' => $data['notes'] ?? null,
                'status' => $status,
                'created_by' => Auth::id(),
            ]);

            if ($request->hasFile('file')) {
                $this->storeFileOnDocument($document, $request->file('file'));
            }

            DB::commit();

            return redirect()->route('vehicles.show', $vehicle)
                ->with('toast_success', 'Vehicle document added successfully.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->withInput()->with('toast_error', 'Failed to add document: '.$e->getMessage());
        }
    }

    public function update(Request $request, Vehicle $vehicle, VehicleDocument $document)
    {
        $this->assertDocumentBelongs($vehicle, $document);
        $data = $this->validated($request);

        try {
            DB::beginTransaction();

            $status = $data['status'] ?? $document->status;
            if ($status !== 'archived' && ! empty($data['expiry_date'])) {
                $status = \Carbon\Carbon::parse($data['expiry_date'])->lt(now()->startOfDay())
                    ? 'expired'
                    : (($status === 'expired') ? 'active' : $status);
            }

            $document->update([
                'document_type' => $data['document_type'],
                'document_number' => $data['document_number'] ?? null,
                'document_name' => $data['document_name'],
                'issue_date' => $data['issue_date'] ?? null,
                'expiry_date' => $data['expiry_date'] ?? null,
                'issuing_authority' => $data['issuing_authority'] ?? null,
                'notes' => $data['notes'] ?? null,
                'status' => $status,
            ]);

            if ($request->hasFile('file')) {
                $this->storeFileOnDocument($document, $request->file('file'), true);
            }

            DB::commit();

            return redirect()->route('vehicles.show', $vehicle)
                ->with('toast_success', 'Document updated successfully.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->withInput()->with('toast_error', 'Failed to update document: '.$e->getMessage());
        }
    }

    public function destroy(Vehicle $vehicle, VehicleDocument $document)
    {
        $this->assertDocumentBelongs($vehicle, $document);

        try {
            $this->deleteDocumentFile($document);
            $document->delete();

            return redirect()->route('vehicles.show', $vehicle)
                ->with('toast_success', 'Document deleted successfully.');
        } catch (\Throwable $e) {
            return back()->with('toast_error', 'Failed to delete document: '.$e->getMessage());
        }
    }

    public function download(Vehicle $vehicle, VehicleDocument $document)
    {
        $this->assertDocumentBelongs($vehicle, $document);

        if (! $document->hasFile()) {
            return back()->with('toast_error', 'File not found.');
        }

        return Storage::disk('private')->download(
            $document->file_path,
            $document->file_name ?: basename($document->file_path)
        );
    }

    /**
     * @return array<string, mixed>
     */
    protected function validated(Request $request): array
    {
        return $request->validate([
            'document_type' => ['required', Rule::in(VehicleDocument::TYPES)],
            'document_name' => ['required', 'string', 'max:255'],
            'document_number' => ['nullable', 'string', 'max:100'],
            'issue_date' => ['nullable', 'date'],
            'expiry_date' => ['nullable', 'date'],
            'issuing_authority' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
            'status' => ['nullable', Rule::in(['active', 'expired', 'pending_renewal', 'archived'])],
            'file' => ['nullable', 'file', 'max:10240', 'mimes:pdf,jpg,jpeg,png'],
        ]);
    }

    protected function assertDocumentBelongs(Vehicle $vehicle, VehicleDocument $document): void
    {
        if ($document->vehicle_id !== $vehicle->id) {
            abort(404);
        }
    }

    protected function storeFileOnDocument(VehicleDocument $document, $file, bool $replace = false): void
    {
        if ($replace) {
            $this->deleteDocumentFile($document);
        }

        $originalName = $file->getClientOriginalName();
        $path = $file->storeAs(
            'vehicle_documents/'.$document->vehicle_id,
            now()->format('YmdHis').'_'.$document->id.'_'.$originalName,
            'private'
        );

        $document->update([
            'file_path' => $path,
            'file_name' => $originalName,
            'file_size' => $file->getSize(),
            'file_uploaded_at' => now(),
        ]);
    }

    protected function deleteDocumentFile(VehicleDocument $document): void
    {
        if ($document->file_path && Storage::disk('private')->exists($document->file_path)) {
            Storage::disk('private')->delete($document->file_path);
        }
    }
}
