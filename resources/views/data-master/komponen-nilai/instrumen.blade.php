@extends('layouts.app')
@section('title', 'OSCE - Instrumen Penilaian')
@section('contents')
<div class="card"><div class="card-body">
    @include('components.alert')
    <p class="text-muted">Langkah 1: Komponen tersimpan / Langkah 2: Instrumen Penilaian</p>
    <h4>Kelola Instrumen Penilaian</h4>
    <p>Jenis OSCE: {{ $komponen->jenis->nama_jenis_osce }}</p>
    <h5>{{ $komponen->nama_komponen }}</h5>
    <p>{{ strip_tags($komponen->detail_komponen) }}</p>
    <p>Bobot: <strong>{{ $komponen->bobot_nilai }}</strong></p>
    @can('update data-master/komponen-nilai-osce')
    <a class="btn btn-outline-secondary" href="{{ route('data-master.komponen-nilai-osce.edit', [encrypt($komponen->id_komponen_nilai_osce), encrypt($komponen->id_jenis_osce)]) }}">Edit Data Komponen</a>
    @endcan
</div></div>
<div class="card"><div class="card-body">
    <div id="page-message" class="alert d-none" role="alert"></div>
    <p>Tambahkan keterangan penilaian untuk setiap skor. Simpan instrumen satu per satu.</p>
    <button type="button" class="btn btn-primary mb-3 instrument-form" data-url="{{ route('data-master.komponen-nilai-osce.formInstrumen', encrypt($komponen->id_komponen_nilai_osce)) }}">Tambah Instrumen</button>
    <div class="table-responsive"><table class="table table-bordered">
        <thead><tr><th>Nilai</th><th>Keterangan Penilaian</th><th>Aksi</th></tr></thead>
        <tbody>
        @forelse($komponen->instrumenNilai as $item)
            <tr><td>{{ $item->nilai }}</td><td style="white-space: pre-line">{{ $item->keterangan }}</td><td>
                <button type="button" class="btn btn-sm btn-warning instrument-form" data-url="{{ route('data-master.komponen-nilai-osce.editInstrumen', encrypt($item->id)) }}">Edit</button>
                @can('delete data-master/komponen-nilai-osce')
                <button type="button" class="btn btn-sm btn-danger delete-instrument" data-url="{{ route('data-master.komponen-nilai-osce.destroyInstrumen', encrypt($item->id)) }}">Hapus</button>
                @endcan
            </td></tr>
        @empty
            <tr><td colspan="3" class="text-center">Belum ada instrumen. Klik Tambah Instrumen untuk mulai mengisi.</td></tr>
        @endforelse
        </tbody>
    </table></div>
    <a class="btn btn-secondary" href="{{ route('data-master.komponen-nilai-osce.index', encrypt($komponen->id_jenis_osce)) }}">Selesai &amp; Kembali ke Daftar</a>
</div></div>
@endsection
@push('modal')
<div class="modal fade" id="instrumentModal" tabindex="-1" aria-label="Form instrumen"><div class="modal-dialog modal-lg"></div></div>
@endpush
@push('scripts')
<script>
$(function () {
    const modal = new bootstrap.Modal(document.getElementById('instrumentModal'));
    function message(text, success) {
        $('#page-message').removeClass('d-none alert-danger alert-success').addClass(success ? 'alert-success' : 'alert-danger').text(text);
    }
    $('.instrument-form').on('click', function () {
        const button = $(this).prop('disabled', true);
        $.get(button.data('url')).done(function (html) {
            $('#instrumentModal .modal-dialog').html(html);
            modal.show();
        }).fail(function () { message('Gagal membuka form instrumen. Coba lagi.', false); })
          .always(function () { button.prop('disabled', false); });
    });
    $(document).on('submit', '#formInstrumen', function (event) {
        event.preventDefault();
        const form = $(this), button = form.find('[type="submit"]');
        button.prop('disabled', true);
        form.find('.field-error').text('');
        $('#form-error').addClass('d-none');
        $.ajax({url: form.attr('action'), method: 'POST', data: form.serialize(), headers: {'Accept': 'application/json'}})
            .done(function () { window.location.reload(); })
            .fail(function (xhr) {
                const data = xhr.responseJSON || {};
                $('#form-error').removeClass('d-none').text(data.message || 'Gagal menyimpan. Coba lagi.');
                const errors = data.errors || {};
                form.find('.field-error').each(function () { $(this).text((errors[$(this).data('error')] || []).join(' ')); });
            }).always(function () { button.prop('disabled', false); });
    });
    $('.delete-instrument').on('click', function () {
        if (!window.confirm('Hapus instrumen ini? Data yang dihapus tidak dapat dikembalikan.')) return;
        const button = $(this).prop('disabled', true);
        $.ajax({url: button.data('url'), method: 'DELETE', headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'), 'Accept': 'application/json'}})
            .done(function () { window.location.reload(); })
            .fail(function (xhr) { message((xhr.responseJSON || {}).message || 'Gagal menghapus instrumen.', false); })
            .always(function () { button.prop('disabled', false); });
    });
});
</script>
@endpush