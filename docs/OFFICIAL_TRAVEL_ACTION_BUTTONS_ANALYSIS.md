# Analisis Pengkondisian Action Button - Official Travel Show View

## Lokasi File
`resources/views/officialtravels/show.blade.php` (Baris 408-466)

## Struktur Hierarki Kondisi

### 1. **Back to List Button** (Selalu Tampil)
```blade
<a href="{{ route('officialtravels.index') }}" class="btn-action back-btn">
    <i class="fas fa-arrow-left"></i> Back to List
</a>
```
- **Kondisi**: Tidak ada kondisi, selalu tampil
- **Permission**: Tidak ada permission check

---

### 2. **Kondisi Utama: Status != 'canceled'**
```blade
@if ($officialtravel->status != 'canceled')
```
**Penjelasan**: Semua action button (kecuali Back dan Print) hanya tampil jika status bukan 'canceled'.

---

### 3. **Kondisi Status: 'draft'**

#### 3.1. **Edit Button**
```blade
@if ($officialtravel->status == 'draft')
    @can('official-travels.edit')
        <a href="{{ route('officialtravels.edit', $officialtravel->id) }}" class="btn-action edit-btn">
            <i class="fas fa-edit"></i> Edit
        </a>
    @endcan
```
- **Kondisi Status**: `status == 'draft'`
- **Permission**: `official-travels.edit`
- **Aksi**: Link ke halaman edit

#### 3.2. **Delete Button**
```blade
@if ($officialtravel->status == 'draft')
    @can('official-travels.delete')
        <button type="button" class="btn-action delete-btn" data-toggle="modal" data-target="#deleteModal">
            <i class="fas fa-trash"></i> Delete
        </button>
    @endcan
```
- **Kondisi Status**: `status == 'draft'`
- **Permission**: `official-travels.delete`
- **Aksi**: Membuka modal delete (`#deleteModal`)

#### 3.3. **Submit for Approval Button**
```blade
@if ($officialtravel->status == 'draft')
    <button type="button" class="btn-action submit-btn" data-toggle="modal" data-target="#submitModal">
        <i class="fas fa-paper-plane"></i> Submit for Approval
    </button>
@endif
```
- **Kondisi Status**: `status == 'draft'` (duplikat check - sudah dalam blok `@if ($officialtravel->status == 'draft')`)
- **Permission**: Tidak ada permission check
- **Aksi**: Membuka modal submit (`#submitModal`)
- **Catatan**: Ada redundansi kondisi karena sudah berada dalam blok `@if ($officialtravel->status == 'draft')`

---

### 4. **Kondisi Status: 'approved'**

#### 4.1. **Record Arrival Button**
```blade
@if ($officialtravel->status == 'approved')
    @can('official-travels.stamp')
        @if ($officialtravel->canRecordArrival())
            <a href="{{ route('officialtravels.showArrivalForm', $officialtravel->id) }}" class="btn-action arrival-btn">
                <i class="fas fa-plane-arrival"></i> Record Arrival
            </a>
        @endif
    @endcan
@endif
```
- **Kondisi Status**: `status == 'approved'`
- **Permission**: `official-travels.stamp`
- **Method Check**: `canRecordArrival()`
- **Aksi**: Link ke form record arrival

**Logika `canRecordArrival()`**:
- Status harus `'approved'`
- Jika belum ada stop (`latestStop` null) → **TRUE** (bisa record arrival pertama)
- Jika latest stop sudah complete (ada arrival & departure) → **TRUE** (bisa record arrival stop baru)
- Jika latest stop belum ada arrival → **TRUE** (bisa record arrival untuk stop yang belum complete)
- Jika latest stop sudah ada arrival tapi belum ada departure → **FALSE**

#### 4.2. **Record Departure Button**
```blade
@if ($officialtravel->status == 'approved')
    @can('official-travels.stamp')
        @if ($officialtravel->canRecordDeparture())
            <a href="{{ route('officialtravels.showDepartureForm', $officialtravel->id) }}" class="btn-action departure-btn">
                <i class="fas fa-plane-departure"></i> Record Departure
            </a>
        @endif
    @endcan
@endif
```
- **Kondisi Status**: `status == 'approved'`
- **Permission**: `official-travels.stamp`
- **Method Check**: `canRecordDeparture()`
- **Aksi**: Link ke form record departure

**Logika `canRecordDeparture()`**:
- Status harus `'approved'`
- Jika belum ada stop (`latestStop` null) → **FALSE** (harus ada arrival dulu)
- Jika latest stop sudah ada arrival tapi belum ada departure → **TRUE**
- Jika latest stop sudah complete → **FALSE** (harus buat stop baru dulu)

#### 4.3. **Close Official Travel Button**
```blade
@if ($officialtravel->status == 'approved')
    @can('official-travels.stamp')
        @if ($officialtravel->canClose())
            <button type="button" class="btn-action close-btn" data-toggle="modal" data-target="#closeModal">
                <i class="fas fa-lock"></i> Close Official Travel
            </button>
        @endif
    @endcan
@endif
```
- **Kondisi Status**: `status == 'approved'`
- **Permission**: `official-travels.stamp`
- **Method Check**: `canClose()`
- **Aksi**: Membuka modal close (`#closeModal`)

**Logika `canClose()`**:
- Status harus `'approved'`
- Harus sudah ada stop (`latestStop` tidak null)
- Latest stop harus complete (ada arrival & departure) → **TRUE**

---

### 5. **Print Button** (Selalu Tampil)
```blade
<a href="{{ route('officialtravels.print', $officialtravel->id) }}" class="btn btn-primary" target="_blank">
    <i class="fas fa-print"></i> Print
</a>
```
- **Kondisi**: Tidak ada kondisi, selalu tampil
- **Permission**: Tidak ada permission check
- **Aksi**: Link ke halaman print (buka di tab baru)

---

## Status yang Didukung

Berdasarkan `$statusMap` di baris 13-32:
1. **'draft'** → Label: 'Draft', Badge: secondary
2. **'submitted'** → Label: 'Submitted', Badge: info
3. **'approved'** → Label: 'Open', Badge: success
4. **'rejected'** → Label: 'Rejected', Badge: danger
5. **'closed'** → Label: 'Closed', Badge: primary
6. **'cancelled'** → Label: 'Cancelled', Badge: warning

---

## Flow Diagram Kondisi

```
Status Check
│
├─ status == 'canceled' → Hanya tampil: Back, Print
│
└─ status != 'canceled'
   │
   ├─ status == 'draft'
   │  ├─ Permission: official-travels.edit → Edit Button
   │  ├─ Permission: official-travels.delete → Delete Button
   │  └─ (No Permission) → Submit for Approval Button
   │
   └─ status == 'approved'
      └─ Permission: official-travels.stamp
         ├─ canRecordArrival() → Record Arrival Button
         ├─ canRecordDeparture() → Record Departure Button
         └─ canClose() → Close Official Travel Button
```

---

## Masalah yang Ditemukan

### 1. **Redundansi Kondisi pada Submit Button**
```blade
@if ($officialtravel->status == 'draft')  // Line 414
    ...
    @if ($officialtravel->status == 'draft')  // Line 430 - REDUNDANT
        <button>Submit for Approval</button>
    @endif
@endif
```
**Rekomendasi**: Hapus kondisi duplikat di line 430 karena sudah berada dalam blok `@if ($officialtravel->status == 'draft')`.

### 2. **Status Lain yang Tidak Ditangani**
- **'submitted'**: Tidak ada action button khusus
- **'rejected'**: Tidak ada action button khusus
- **'closed'**: Tidak ada action button khusus

**Catatan**: Ini mungkin sudah sesuai dengan business logic, tetapi perlu dikonfirmasi.

### 3. **Print Button Tidak Ada Permission Check**
Print button selalu tampil tanpa permission check. Jika diperlukan, bisa ditambahkan:
```blade
@can('official-travels.print')
    <a href="...">Print</a>
@endcan
```

---

## Rekomendasi Perbaikan

### 1. **Hapus Redundansi Submit Button**
```blade
@if ($officialtravel->status == 'draft')
    @can('official-travels.edit')
        <a href="...">Edit</a>
    @endcan

    @can('official-travels.delete')
        <button>Delete</button>
    @endcan

    <!-- Hapus @if ($officialtravel->status == 'draft') di sini -->
    <button type="button" class="btn-action submit-btn" data-toggle="modal" data-target="#submitModal">
        <i class="fas fa-paper-plane"></i> Submit for Approval
    </button>
@endif
```

### 2. **Tambahkan Permission Check untuk Submit (Opsional)**
Jika diperlukan permission untuk submit:
```blade
@can('official-travels.submit')
    <button>Submit for Approval</button>
@endcan
```

### 3. **Tambahkan Permission Check untuk Print (Opsional)**
```blade
@can('official-travels.print')
    <a href="...">Print</a>
@endcan
```

---

## Summary

| Button | Status Required | Permission Required | Method Check | Modal/Route |
|--------|----------------|-------------------|--------------|-------------|
| Back to List | - | - | - | Route: `officialtravels.index` |
| Edit | `draft` | `official-travels.edit` | - | Route: `officialtravels.edit` |
| Delete | `draft` | `official-travels.delete` | - | Modal: `#deleteModal` |
| Submit for Approval | `draft` | - | - | Modal: `#submitModal` |
| Record Arrival | `approved` | `official-travels.stamp` | `canRecordArrival()` | Route: `officialtravels.showArrivalForm` |
| Record Departure | `approved` | `official-travels.stamp` | `canRecordDeparture()` | Route: `officialtravels.showDepartureForm` |
| Close Official Travel | `approved` | `official-travels.stamp` | `canClose()` | Modal: `#closeModal` |
| Print | - | - | - | Route: `officialtravels.print` |
