<script>
(() => {
  const data = @js($panduanModalData);
  const modal = document.getElementById('modal-panduan-form');
  const container = document.getElementById('modal-panduan-form-container');
  const form = document.getElementById('form-panduan-modal');
  const methodInput = document.getElementById('panduan-modal-method');
  const title = document.getElementById('modal-panduan-form-title');
  const subtitle = document.getElementById('modal-panduan-form-subtitle');
  const submitText = document.querySelector('#btn-submit-panduan-modal span');
  const errorBox = document.getElementById('panduan-modal-errors');
  const imageBox = document.getElementById('panduan-modal-current-image');
  const imagePreview = document.getElementById('panduan-modal-current-image-preview');
  const deleteModal = document.getElementById('modal-delete-panduan');
  const deleteContainer = document.getElementById('modal-delete-panduan-container');
  const deleteForm = document.getElementById('form-delete-panduan');
  const deleteName = document.getElementById('delete-panduan-nama');

  if (!modal || !container || !form || !deleteModal || !deleteForm) return;

  const fields = {
    judul: document.getElementById('panduan-modal-judul'),
    kategori: document.getElementById('panduan-modal-kategori'),
    komoditas: document.getElementById('panduan-modal-komoditas'),
    tanggal: document.getElementById('panduan-modal-tanggal'),
    status: document.getElementById('panduan-modal-status'),
    ringkasan: document.getElementById('panduan-modal-ringkasan'),
    isi: document.getElementById('panduan-modal-isi'),
    gambar: document.getElementById('panduan-modal-gambar'),
  };

  function setErrors(messages) {
    if (!messages || !messages.length) {
      errorBox.classList.add('hidden');
      errorBox.innerHTML = '';
      return;
    }
    errorBox.innerHTML = '<p class="font-bold">Periksa kembali data yang diisi.</p><ul class="mt-1 list-inside list-disc"></ul>';
    const list = errorBox.querySelector('ul');
    messages.forEach(message => {
      const li = document.createElement('li');
      li.textContent = message;
      list.appendChild(li);
    });
    errorBox.classList.remove('hidden');
  }

  function resetFields() {
    form.reset();
    methodInput.value = 'POST';
    form.action = @json(route('admin.edukasi.panduan.store'));
    fields.tanggal.value = new Date().toISOString().slice(0, 10);
    fields.status.value = 'draft';
    imageBox.classList.add('hidden');
    imageBox.classList.remove('flex');
    imagePreview.src = '';
    setErrors([]);
  }

  function openModal() {
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    modal.setAttribute('aria-hidden', 'false');
    document.body.classList.add('overflow-hidden');
    requestAnimationFrame(() => {
      container.classList.remove('scale-95');
      container.classList.add('scale-100');
    });
    if (window.lucide) window.lucide.createIcons();
    setTimeout(() => fields.judul.focus(), 50);
  }

  function closeModal() {
    container.classList.remove('scale-100');
    container.classList.add('scale-95');
    setTimeout(() => {
      modal.classList.add('hidden');
      modal.classList.remove('flex');
      modal.setAttribute('aria-hidden', 'true');
      document.body.classList.remove('overflow-hidden');
    }, 150);
  }

  window.openPanduanCreateModal = function () {
    resetFields();
    title.textContent = 'Tambah Data Panduan';
    subtitle.textContent = 'Simpan data panduan ke database';
    submitText.textContent = 'Simpan Panduan';
    openModal();
  };

  window.openPanduanEditModal = function (id, itemOverride = null) {
    const item = itemOverride || data[String(id)];
    if (!item) return;

    form.reset();
    methodInput.value = 'PUT';
    form.action = item.update_url;
    fields.judul.value = item.judul || '';
    fields.kategori.value = item.kategori || '';
    fields.komoditas.value = item.komoditas || '';
    fields.tanggal.value = item.tanggal || '';
    fields.status.value = item.status || 'draft';
    fields.ringkasan.value = item.ringkasan || '';
    fields.isi.value = item.isi || '';
    fields.gambar.value = '';
    setErrors([]);

    title.textContent = 'Edit Data Panduan';
    subtitle.textContent = 'Perbarui data panduan yang tersimpan';
    submitText.textContent = 'Simpan Perubahan';

    if (item.gambar) {
      imagePreview.src = item.gambar;
      imageBox.classList.remove('hidden');
      imageBox.classList.add('flex');
    } else {
      imageBox.classList.add('hidden');
      imageBox.classList.remove('flex');
      imagePreview.src = '';
    }

    openModal();
  };

  window.openPanduanDeleteModal = function (id, itemOverride = null) {
    const item = itemOverride || data[String(id)];
    if (!item) return;

    deleteForm.action = item.delete_url;
    deleteName.textContent = item.judul || 'ini';
    deleteModal.classList.remove('hidden');
    deleteModal.classList.add('flex');
    deleteModal.setAttribute('aria-hidden', 'false');
    document.body.classList.add('overflow-hidden');
    requestAnimationFrame(() => {
      deleteContainer.classList.remove('scale-95');
      deleteContainer.classList.add('scale-100');
    });
    if (window.lucide) window.lucide.createIcons();
  };

  function closeDeleteModal() {
    deleteContainer.classList.remove('scale-100');
    deleteContainer.classList.add('scale-95');
    setTimeout(() => {
      deleteModal.classList.add('hidden');
      deleteModal.classList.remove('flex');
      deleteModal.setAttribute('aria-hidden', 'true');
      document.body.classList.remove('overflow-hidden');
    }, 150);
  }

  document.querySelectorAll('[data-panduan-modal-close]').forEach(button => button.addEventListener('click', closeModal));
  document.querySelector('[data-panduan-modal-backdrop]')?.addEventListener('click', closeModal);
  document.querySelector('[data-panduan-delete-close]')?.addEventListener('click', closeDeleteModal);
  document.querySelector('[data-panduan-delete-backdrop]')?.addEventListener('click', closeDeleteModal);

  form.addEventListener('submit', async (event) => {
    event.preventDefault();
    setErrors([]);

    const submitButton = document.getElementById('btn-submit-panduan-modal');
    const originalButton = submitButton.innerHTML;
    submitButton.disabled = true;
    submitButton.innerHTML = '<span class="h-4 w-4 animate-spin rounded-full border-2 border-white/40 border-t-white"></span><span>Menyimpan...</span>';

    try {
      const response = await fetch(form.action, {
        method: 'POST',
        body: new FormData(form),
        headers: {
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
          'X-Requested-With': 'XMLHttpRequest',
          'Accept': 'application/json'
        }
      });

      const contentType = response.headers.get('content-type') || '';
      const payload = contentType.includes('application/json') ? await response.json() : null;

      if (!response.ok) {
        if (payload?.errors) {
          setErrors(Object.values(payload.errors).flat());
          return;
        }
        throw new Error(payload?.message || 'Gagal menyimpan panduan.');
      }

      closeModal();
      window.location.href = payload?.redirect || @json(route('admin.edukasi.panduan'));
    } catch (error) {
      setErrors([error.message || 'Terjadi kesalahan. Silakan coba lagi.']);
    } finally {
      submitButton.disabled = false;
      submitButton.innerHTML = originalButton;
      if (window.lucide) window.lucide.createIcons();
    }
  });

  document.addEventListener('keydown', (event) => {
    if (event.key !== 'Escape') return;
    if (!modal.classList.contains('hidden')) closeModal();
    if (!deleteModal.classList.contains('hidden')) closeDeleteModal();
  });

  if (window.lucide) window.lucide.createIcons();
})();
</script>