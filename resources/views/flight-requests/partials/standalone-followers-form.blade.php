@php
    $existingFollowers = $existingFollowers ?? collect();
    $oldFollowers = old('followers');
    $followerRows = [];

    if (is_array($oldFollowers) && count($oldFollowers) > 0) {
        foreach ($oldFollowers as $idx => $row) {
            $followerRows[] = ['source' => 'old', 'index' => $idx, 'data' => $row];
        }
    } elseif ($existingFollowers->isNotEmpty()) {
        foreach ($existingFollowers as $idx => $follower) {
            $followerRows[] = [
                'source' => 'db',
                'index' => $idx,
                'data' => [
                    'is_manual' => $follower->isManual() ? '1' : '0',
                    'administration_id' => $follower->administration_id,
                    'title' => $follower->title,
                    'follower_name' => $follower->follower_name,
                    'nik' => $follower->nik,
                    'phone_number' => $follower->phone_number,
                ],
            ];
        }
    }
@endphp

<div class="card card-success card-outline elevation-3 mb-3" id="standalone_followers_card" style="display: none;">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-users mr-2"></i>
            <strong>Followers</strong>
            <small class="text-muted ml-2">(optional — standalone only)</small>
        </h3>
        <div class="card-tools">
            <button type="button" class="btn btn-tool" id="addStandaloneFollowerRow">
                <i class="fas fa-plus"></i> Add Follower
            </button>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-striped table-hover mb-0" id="standaloneFollowersTable">
                <thead>
                    <tr>
                        <th style="min-width: 200px;">Employee / Manual</th>
                        <th style="width: 90px;">Title</th>
                        <th>Name</th>
                        <th style="min-width: 120px;">NIK / KTP</th>
                        <th style="min-width: 120px;">Phone</th>
                        <th width="50px">Action</th>
                    </tr>
                </thead>
                <tbody id="standaloneFollowersTableBody">
                    @foreach ($followerRows as $row)
                        @include('flight-requests.partials.standalone-follower-row', [
                            'rowIndex' => $row['index'],
                            'row' => $row['data'],
                            'followerEmployeeOptions' => $followerEmployeeOptions,
                        ])
                    @endforeach
                </tbody>
            </table>
        </div>
        <p class="text-muted small px-3 py-2 mb-0">
            <i class="fas fa-info-circle"></i>
            Select an employee to fill <strong>Name</strong>, ID number, and <strong>Phone</strong> automatically.
            Use the checkbox in each row to enter those fields manually instead.
        </p>
    </div>
</div>

<template id="standaloneFollowerRowTemplate">
    @include('flight-requests.partials.standalone-follower-row', [
        'rowIndex' => '__INDEX__',
        'row' => [],
        'followerEmployeeOptions' => $followerEmployeeOptions,
    ])
</template>
