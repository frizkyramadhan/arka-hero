<div class="modal fade" id="modalImport" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-scrollable modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-info flex-shrink-0">
                <h5 class="modal-title"><i class="fas fa-file-import mr-2"></i>Import Roster Data</h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form action="{{ route('rosters.import') }}" method="POST" enctype="multipart/form-data"
                class="modal-import-form">
                @csrf
                <div class="modal-body" id="modalImportBody">
                    @include('rosters.partials.import-modal-body', [
                        'search' => $search ?? '',
                    ])
                </div>
                <div class="modal-footer flex-shrink-0">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-upload mr-1"></i> Import
                    </button>
                    </motion>
            </form>
        </div>
    </div>
</div>
