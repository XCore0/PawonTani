<!-- Modal Form Tambah / Edit Panduan -->
<div id="modal-panduan-form" class="fixed inset-0 z-[120] hidden items-center justify-center bg-slate-900/50 p-4 backdrop-blur-[2px] sm:p-6" aria-hidden="true">
  <div class="absolute inset-0" data-panduan-modal-backdrop></div>

  <div id="modal-panduan-form-container" role="dialog" aria-modal="true" aria-labelledby="modal-panduan-form-title"
    class="relative flex max-h-[92vh] w-full max-w-xl scale-95 flex-col overflow-hidden rounded-[22px] border border-[#E4F0D6] bg-white shadow-2xl transition-transform duration-200">

    <div class="flex shrink-0 items-center justify-between border-b border-[#E4F0D6] bg-[#F5F8F1] px-6 py-4">
      <div class="flex min-w-0 items-center gap-2.5">
        <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-[#EBF6E0] text-[#4D9830]">
          <i data-lucide="book-open-plus" class="h-4 w-4"></i>
        </div>
        <div class="min-w-0">
          <h3 id="modal-panduan-form-title" class="text-base font-bold text-[#1A2D10]">Tambah Data Panduan</h3>
          <p id="modal-panduan-form-subtitle" class="text-xs text-[#9AB880]">Simpan data panduan ke database</p>
        </div>
      </div>
      <button type="button" data-panduan-modal-close class="rounded-lg p-1 text-slate-400 transition-colors hover:bg-white hover:text-slate-700" aria-label="Tutup">
        <i data-lucide="x" class="h-5 w-5"></i>
      </button>
    </div>

    <form id="form-panduan-modal" method="POST" action="{{ route('admin.edukasi.panduan.store') }}" enctype="multipart/form-data" class="flex min-h-0 flex-1 flex-col">
      @csrf
      <input type="hidden" name="_method" id="panduan-modal-method" value="POST">

      <div class="min-h-0 flex-1 overflow-y-auto px-6 py-5">
        <div id="panduan-modal-errors" class="mb-4 hidden rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-xs text-red-700"></div>

        <div class="grid grid-cols-1 gap-3.5 sm:grid-cols-2">
          <div class="sm:col-span-2">
            <label for="panduan-modal-judul" class="mb-1 block text-xs font-semibold text-[#1A2D10]">Judul Panduan <span class="text-red-500">*</span></label>
            <input id="panduan-modal-judul" name="judul" type="text" required maxlength="255" placeholder="Contoh: Panduan Budidaya Jagung Hibrida"
              class="h-10.5 w-full rounded-xl border border-[#C5DFB0] bg-white px-3.5 text-sm text-slate-800 placeholder-[#9AB880] outline-none transition focus:border-[#4D9830] focus:ring-2 focus:ring-[#4D9830]/20">
          </div>

          <div>
            <label for="panduan-modal-kategori" class="mb-1 block text-xs font-semibold text-[#1A2D10]">Kategori <span class="text-red-500">*</span></label>
            <input id="panduan-modal-kategori" name="kategori" type="text" required maxlength="100" placeholder="Contoh: Budidaya"
              class="h-10.5 w-full rounded-xl border border-[#C5DFB0] bg-white px-3.5 text-sm text-slate-800 placeholder-[#9AB880] outline-none transition focus:border-[#4D9830] focus:ring-2 focus:ring-[#4D9830]/20">
          </div>

          <div>
            <label for="panduan-modal-komoditas" class="mb-1 block text-xs font-semibold text-[#1A2D10]">Komoditas</label>
            <input id="panduan-modal-komoditas" name="komoditas" type="text" maxlength="100" placeholder="Contoh: Jagung"
              class="h-10.5 w-full rounded-xl border border-[#C5DFB0] bg-white px-3.5 text-sm text-slate-800 placeholder-[#9AB880] outline-none transition focus:border-[#4D9830] focus:ring-2 focus:ring-[#4D9830]/20">
          </div>

          <div>
            <label for="panduan-modal-tanggal" class="mb-1 block text-xs font-semibold text-[#1A2D10]">Tanggal <span class="text-red-500">*</span></label>
            <input id="panduan-modal-tanggal" name="tanggal" type="date" required
              class="h-10.5 w-full rounded-xl border border-[#C5DFB0] bg-white px-3.5 text-sm text-slate-800 outline-none transition focus:border-[#4D9830] focus:ring-2 focus:ring-[#4D9830]/20">
          </div>

          <div>
            <label for="panduan-modal-status" class="mb-1 block text-xs font-semibold text-[#1A2D10]">Status Panduan <span class="text-red-500">*</span></label>
            <select id="panduan-modal-status" name="status" required
              class="h-10.5 w-full rounded-xl border border-[#C5DFB0] bg-white px-3.5 text-sm text-slate-800 outline-none transition focus:border-[#4D9830] focus:ring-2 focus:ring-[#4D9830]/20">
              <option value="draft">Draft</option>
              <option value="publik">Publik</option>
            </select>
          </div>

          <div class="sm:col-span-2">
            <label for="panduan-modal-ringkasan" class="mb-1 block text-xs font-semibold text-[#1A2D10]">Ringkasan <span class="text-red-500">*</span></label>
            <textarea id="panduan-modal-ringkasan" name="ringkasan" rows="3" required placeholder="Tuliskan ringkasan singkat panduan..."
              class="w-full resize-y rounded-xl border border-[#C5DFB0] bg-white px-3.5 py-2.5 text-sm leading-6 text-slate-800 placeholder-[#9AB880] outline-none transition focus:border-[#4D9830] focus:ring-2 focus:ring-[#4D9830]/20"></textarea>
          </div>

          <div class="sm:col-span-2">
            <label for="panduan-modal-isi" class="mb-1 block text-xs font-semibold text-[#1A2D10]">Isi Panduan <span class="text-red-500">*</span></label>
            <textarea id="panduan-modal-isi" name="isi" rows="8" required placeholder="Tuliskan langkah-langkah atau materi panduan secara lengkap..."
              class="w-full resize-y rounded-xl border border-[#C5DFB0] bg-white px-3.5 py-2.5 text-sm leading-6 text-slate-800 placeholder-[#9AB880] outline-none transition focus:border-[#4D9830] focus:ring-2 focus:ring-[#4D9830]/20"></textarea>
          </div>

          <div class="sm:col-span-2">
            <label for="panduan-modal-gambar" class="mb-1 block text-xs font-semibold text-[#1A2D10]">Gambar <span class="font-normal text-[#9AB880]">(Opsional)</span></label>
            <input id="panduan-modal-gambar" name="gambar" type="file" accept=".jpg,.jpeg,.png,.webp"
              class="block w-full rounded-xl border border-[#C5DFB0] bg-white px-2 py-2 text-xs text-slate-600 file:mr-3 file:rounded-lg file:border-0 file:bg-[#EBF6E0] file:px-3 file:py-2 file:text-xs file:font-bold file:text-[#4D9830]">
            <p class="mt-1.5 text-[11px] text-[#9AB880]">Format JPG, JPEG, PNG, WEBP (maksimal 2 MB).</p>
            <div id="panduan-modal-current-image" class="mt-3 hidden items-center gap-3">
              <img id="panduan-modal-current-image-preview" src="" alt="Gambar panduan" class="h-16 w-24 rounded-xl border border-[#E4F0D6] object-cover">
              <span class="text-[11px] text-[#6B7F5B]">Gambar saat ini. Upload gambar baru untuk menggantinya.</span>
            </div>
          </div>
        </div>
      </div>

      <div class="flex shrink-0 items-center justify-end gap-2 border-t border-[#E4F0D6] bg-white px-6 py-4">
        <button type="button" data-panduan-modal-close class="rounded-xl border border-[#C5DFB0] bg-white px-5 py-2.5 text-xs font-semibold text-[#4A6030] transition-colors hover:bg-[#F5F8F1]">
          Batal
        </button>
        <button type="submit" id="btn-submit-panduan-modal" class="inline-flex items-center justify-center gap-1.5 rounded-xl bg-[#4D9830] px-5 py-2.5 text-xs font-bold text-white shadow-sm transition-all hover:bg-[#3D8024] disabled:cursor-not-allowed disabled:opacity-60">
          <i data-lucide="save" class="h-4 w-4"></i>
          <span>Simpan Panduan</span>
        </button>
      </div>
    </form>
  </div>
</div>

<!-- Modal Konfirmasi Hapus Panduan -->
<div id="modal-delete-panduan" class="fixed inset-0 z-[130] hidden items-center justify-center bg-slate-900/50 p-4 backdrop-blur-[2px]" aria-hidden="true">
  <div class="absolute inset-0" data-panduan-delete-backdrop></div>
  <div id="modal-delete-panduan-container" role="dialog" aria-modal="true" aria-labelledby="modal-delete-panduan-title"
    class="relative w-full max-w-md scale-95 overflow-hidden rounded-[22px] border border-red-100 bg-white p-6 text-center shadow-2xl transition-transform duration-200">
    <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-red-50 text-red-600">
      <i data-lucide="trash-2" class="h-6 w-6"></i>
    </div>
    <h3 id="modal-delete-panduan-title" class="mt-4 text-lg font-bold text-[#1A2D10]">Hapus Data Panduan?</h3>
    <p class="mt-2 text-xs leading-6 text-[#6B7F5B]">
      Apakah Anda yakin ingin menghapus panduan <span id="delete-panduan-nama" class="font-bold text-[#1A2D10]"></span>?
    </p>
    <div class="mt-3 flex items-start gap-2 rounded-xl border border-red-100 bg-red-50/70 p-3 text-left text-[11px] leading-5 text-red-700">
      <i data-lucide="info" class="mt-0.5 h-4 w-4 shrink-0 text-red-500"></i>
      <span>Tindakan ini tidak dapat dibatalkan. Data panduan dan gambar yang terkait akan dihapus dari sistem.</span>
    </div>
    <form id="form-delete-panduan" method="POST" class="mt-5 flex items-center justify-center gap-3">
      @csrf
      @method('DELETE')
      <button type="button" data-panduan-delete-close class="w-1/2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-xs font-semibold text-slate-700 transition-colors hover:bg-slate-50">Batal</button>
      <button type="submit" class="inline-flex w-1/2 items-center justify-center gap-1.5 rounded-xl bg-red-600 px-4 py-2.5 text-xs font-bold text-white shadow-sm shadow-red-500/20 transition-all hover:bg-red-700">
        <i data-lucide="trash-2" class="h-4 w-4"></i>
        Ya, Hapus
      </button>
    </form>
  </div>
</div>
