@extends('layouts.app')
@section('title', 'Pengaturan')

@section('content')
<div class="grid-2">
    {{-- Store Settings --}}
    <div class="card">
        <div class="card-header"><h2><i class="ri-store-2-fill"></i> Pengaturan Toko</h2></div>
        <div class="card-body">
            <form method="POST" action="{{ route('settings.update') }}">
                @csrf @method('PUT')
                <div class="form-group">
                    <label class="form-label">Nama Toko</label>
                    <input type="text" name="store_name" class="form-control" value="{{ $settings['store_name']->value ?? 'CargoMind' }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Alamat Toko</label>
                    <textarea name="store_address" class="form-control" required>{{ $settings['store_address']->value ?? '' }}</textarea>
                </div>
                <div class="form-group">
                    <label class="form-label">Telepon</label>
                    <input type="text" name="store_phone" class="form-control" value="{{ $settings['store_phone']->value ?? '' }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Ukuran Font Nota (pt)</label>
                    <input type="number" name="receipt_font_size" class="form-control" value="{{ $settings['receipt_font_size']->value ?? 14 }}" min="10" max="24" required>
                    <small style="color:var(--text-muted);">Default: 14pt. Untuk mata lansia, gunakan 16-18pt.</small>
                </div>
                <div class="form-group">
                    <label class="form-label">Footer Nota</label>
                    <input type="text" name="receipt_footer" class="form-control" value="{{ $settings['receipt_footer']->value ?? 'Terima kasih telah berbelanja di CargoMind' }}">
                </div>
                <button type="submit" class="btn btn-primary"><i class="ri-save-fill"></i> Simpan Pengaturan</button>
            </form>
        </div>
    </div>

    {{-- User Management --}}
    <div class="card">
        <div class="card-header"><h2><i class="ri-team-fill"></i> Manajemen User</h2></div>
        <div class="card-body">
            <form method="POST" action="{{ route('settings.users.create') }}" class="mb-3" style="border-bottom:1px solid var(--border-light);padding-bottom:20px;">
                @csrf
                <h3 style="font-size:1rem;margin-bottom:12px;">Tambah User Baru</h3>
                <div class="form-group"><label class="form-label">Nama</label><input type="text" name="name" class="form-control" required></div>
                <div class="form-group"><label class="form-label">Email</label><input type="email" name="email" class="form-control" required></div>
                <div class="form-group"><label class="form-label">Password</label><input type="password" name="password" class="form-control" required minlength="6"></div>
                <div class="form-group">
                    <label class="form-label">Role</label>
                    <select name="role" class="form-control" required>
                        <option value="karyawan">Karyawan</option>
                        <option value="manager">Manager</option>
                        <option value="master">Master</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary btn-sm"><i class="ri-user-add-fill"></i> Tambah</button>
            </form>

            <h3 style="font-size:1rem;margin-bottom:12px;">Daftar User</h3>
            <div class="table-wrapper">
                <table class="table">
                    <thead><tr><th>Nama</th><th>Email</th><th>Role</th><th class="text-center">Aksi</th></tr></thead>
                    <tbody>
                    @foreach($users as $user)
                    <tr>
                        <td><strong>{{ $user->name }}</strong></td>
                        <td>{{ $user->email }}</td>
                        <td><span class="badge badge-success">{{ ucfirst($user->role) }}</span></td>
                        <td class="text-center">
                            @if($user->id !== auth()->id())
                            <form method="POST" action="{{ route('settings.users.delete', $user) }}" style="display:inline;" onsubmit="return confirm('Hapus user ini?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-danger"><i class="ri-delete-bin-fill"></i></button>
                            </form>
                            @else <small style="color:var(--text-muted);">Anda</small> @endif
                        </td>
                    </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
