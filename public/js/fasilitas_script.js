// public/js/fasilitas_script.js

document.addEventListener('DOMContentLoaded', () => {
    const fasilitasTableBody = document.getElementById('fasilitasTableBody');
    const modal = document.getElementById('fasilitasModal');
    const modalTitle = document.getElementById('modalTitle');
    const closeButton = document.querySelector('.close-button');
    const fasilitasForm = document.getElementById('fasilitasForm');
    const fasilitasIdInput = document.getElementById('fasilitasId');
    const namaFasilitasInput = document.getElementById('namaFasilitas');
    const gambarIkonInput = document.getElementById('gambarIkon');
    const currentImageInfo = document.getElementById('currentImageInfo');
    const previewImage = document.getElementById('previewImage');
    const addFasilitasBtn = document.querySelector('.btn-add-fasilitas');
    const searchInput = document.getElementById('search-fasilitas');
    const searchButton = searchInput.nextElementSibling; // Tombol search setelah input

    let isEditMode = false;

    function openModal() {
        modal.style.display = 'flex';
    }

    function closeModal() {
        modal.style.display = 'none';
        fasilitasForm.reset();
        fasilitasIdInput.value = '';
        namaFasilitasInput.value = ''; // Pastikan nama fasilitas juga dikosongkan
        previewImage.style.display = 'none';
        previewImage.src = '';
        currentImageInfo.textContent = '';
        isEditMode = false;
        gambarIkonInput.value = ''; // Pastikan input file dikosongkan
        // Pastikan ini ada di sini, di akhir closeModal() dan TIDAK dikomentari
        currentImageInfo.removeAttribute('data-original-image');
        currentImageInfo.removeAttribute('data-original-image-url');
    }

    addFasilitasBtn.addEventListener('click', () => {
        modalTitle.textContent = 'Tambah Fasilitas Baru';
        closeModal(); // Pastikan form bersih sebelum membuka modal baru
        openModal();
    });

    closeButton.addEventListener('click', closeModal);

    window.addEventListener('click', (event) => {
        if (event.target == modal) {
            closeModal();
        }
    });

    gambarIkonInput.addEventListener('change', (event) => {
        const file = event.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = (e) => {
                previewImage.src = e.target.result;
                previewImage.style.display = 'block';
                currentImageInfo.textContent = `New: ${file.name}`; // Tampilkan nama file baru
            };
            reader.readAsDataURL(file);
        } else {
            // Jika input file dikosongkan, kembali ke informasi gambar saat ini (jika ada)
            if (fasilitasIdInput.value && currentImageInfo.dataset.originalImage) {
                currentImageInfo.textContent = `Current: ${currentImageInfo.dataset.originalImage}`;
                previewImage.src = currentImageInfo.dataset.originalImageUrl;
                previewImage.style.display = 'block';
            } else {
                previewImage.style.display = 'none';
                previewImage.src = '';
                currentImageInfo.textContent = '';
            }
        }
    });

    function renderTableData(data) {
        fasilitasTableBody.innerHTML = '';

        if (data.length === 0) {
            fasilitasTableBody.innerHTML = '<tr><td colspan="4" style="text-align: center;">Tidak ada data fasilitas.</td></tr>'; // colspan 4 karena sekarang ada kolom nama_fasilitas
            return;
        }

        data.forEach(fasilitas => {
            const row = document.createElement('tr');

            const tdId = document.createElement('td');
            tdId.textContent = `FL${String(fasilitas.id).padStart(2, '0')}`;
            row.appendChild(tdId);

            const tdNama = document.createElement('td'); // Kolom baru untuk nama fasilitas
            tdNama.textContent = fasilitas.nama_fasilitas;
            row.appendChild(tdNama);

            const tdGambar = document.createElement('td');
            if (fasilitas.gambar_ikon && fasilitas.full_gambar_url) {
                const img = document.createElement('img');
                img.src = fasilitas.full_gambar_url;
                img.alt = `Icon ${fasilitas.nama_fasilitas}`;
                img.classList.add('img-icon-small');
                tdGambar.appendChild(img);
                const spanFileName = document.createElement('span');
                spanFileName.textContent = ` ${fasilitas.gambar_ikon}`;
                tdGambar.appendChild(spanFileName);
            } else {
                tdGambar.textContent = 'Tidak ada ikon';
            }
            row.appendChild(tdGambar);

            const tdAksi = document.createElement('td');
            const editBtn = document.createElement('a');
            editBtn.href = "#";
            editBtn.classList.add('btn-action', 'edit');
            editBtn.innerHTML = '<i class="fas fa-edit"></i>';
            editBtn.title = `Edit ${fasilitas.nama_fasilitas}`;
            editBtn.addEventListener('click', async (e) => {
                e.preventDefault();
                isEditMode = true;
                modalTitle.textContent = `Edit Fasilitas: ${fasilitas.nama_fasilitas}`;
                fasilitasIdInput.value = fasilitas.id;
                namaFasilitasInput.value = fasilitas.nama_fasilitas;

                gambarIkonInput.value = ''; // Reset input file

                // Fetch data fasilitas tunggal untuk memastikan data terbaru
                try {
                    const response = await fetch(`/api/fasilitas/${fasilitas.id}`, {
                        headers: {
                            'Accept': 'application/json' // Penting untuk respons JSON
                        }
                    });
                    if (!response.ok) {
                        const errorData = await response.json(); // Coba parse JSON jika ada
                        throw new Error(errorData.message || `HTTP error! status: ${response.status}`);
                    }
                    const data = await response.json();

                    if (data.gambar_ikon && data.full_gambar_url) {
                        currentImageInfo.textContent = `Current: ${data.gambar_ikon}`;
                        currentImageInfo.dataset.originalImage = data.gambar_ikon; // Simpan nama file asli
                        currentImageInfo.dataset.originalImageUrl = data.full_gambar_url; // Simpan URL asli
                        previewImage.src = data.full_gambar_url;
                        previewImage.style.display = 'block';
                    } else {
                        currentImageInfo.textContent = 'Tidak ada ikon saat ini.';
                        currentImageInfo.dataset.originalImage = '';
                        currentImageInfo.dataset.originalImageUrl = '';
                        previewImage.style.display = 'none';
                        previewImage.src = '';
                    }
                } catch (error) {
                    console.error('Gagal mengambil detail fasilitas untuk edit:', error);
                    // Ganti alert dengan Swal.fire
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: `Gagal memuat detail fasilitas untuk diedit: ${error.message}`,
                        showConfirmButton: true
                    }).then(() => {
                        closeModal();
                    });
                    return;
                }

                openModal();
            });

            const deleteBtn = document.createElement('a');
            deleteBtn.href = "#";
            deleteBtn.classList.add('btn-action', 'delete');
            deleteBtn.innerHTML = '<i class="fas fa-trash-alt"></i>';
            deleteBtn.title = `Hapus ${fasilitas.nama_fasilitas}`;
            deleteBtn.addEventListener('click', async (e) => {
                e.preventDefault();
                // Ganti confirm dengan Swal.fire
                Swal.fire({
                    title: 'Apakah Anda yakin?',
                    text: `Anda tidak akan bisa mengembalikan fasilitas "${fasilitas.nama_fasilitas}" ini!`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33', // Warna merah untuk konfirmasi hapus
                    cancelButtonColor: '#3085d6', // Warna biru untuk batal
                    confirmButtonText: 'Ya, hapus!',
                    cancelButtonText: 'Batal'
                }).then(async (result) => {
                    if (result.isConfirmed) {
                        try {
                            const response = await fetch(`/api/fasilitas/${fasilitas.id}`, {
                                method: 'DELETE',
                                headers: {
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                    'Accept': 'application/json' // Penting untuk respons JSON
                                }
                            });

                            if (!response.ok) {
                                const errorData = await response.json();
                                throw new Error(errorData.message || `HTTP error! status: ${response.status}`);
                            }

                            // Ganti alert sukses dengan Swal.fire
                            Swal.fire({
                                icon: 'success',
                                title: 'Dihapus!',
                                text: 'Fasilitas berhasil dihapus.',
                                showConfirmButton: false,
                                timer: 1500
                            }).then(() => {
                                fetchDataFasilitas(); // Muat ulang data setelah penghapusan
                            });
                        } catch (error) {
                            console.error('Gagal menghapus fasilitas:', error);
                            // Ganti alert error dengan Swal.fire
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal!',
                                text: `Gagal menghapus fasilitas: ${error.message}`,
                                showConfirmButton: true
                            });
                        }
                    }
                });
            });

            tdAksi.appendChild(editBtn);
            tdAksi.appendChild(deleteBtn);
            row.appendChild(tdAksi);

            fasilitasTableBody.appendChild(row);
        });
    }

    async function fetchDataFasilitas(searchTerm = '') {
        try {
            const url = searchTerm ? `/api/fasilitas?search=${encodeURIComponent(searchTerm)}` : '/api/fasilitas';
            const response = await fetch(url, {
                 headers: {
                    'Accept': 'application/json' // Penting untuk respons JSON
                }
            });
            if (!response.ok) {
                const errorData = await response.json(); // Coba parse JSON jika ada
                throw new Error(errorData.message || `HTTP error! status: ${response.status}`);
            }
            const data = await response.json();
            renderTableData(data);
        } catch (error) {
            console.error("Gagal mengambil data fasilitas:", error);
            fasilitasTableBody.innerHTML = '<tr><td colspan="4" style="text-align: center; color: red;">Gagal memuat data. Silakan coba lagi.</td></tr>'; // colspan 4
            // Ganti ini dengan SweetAlert2
            Swal.fire({
                icon: 'error',
                title: 'Gagal Memuat Data!',
                text: `Terjadi kesalahan saat memuat data fasilitas: ${error.message}`,
                showConfirmButton: true
            });
        }
    }

    fasilitasForm.addEventListener('submit', async (e) => {
        e.preventDefault();

        const id = fasilitasIdInput.value;
        const url = id ? `/api/fasilitas/${id}` : '/api/fasilitas';
        const method = id ? 'POST' : 'POST'; // Kita akan menggunakan POST dengan _method PUT untuk update

        const formData = new FormData();
        formData.append('nama_fasilitas', namaFasilitasInput.value);

        if (gambarIkonInput.files.length > 0) {
            formData.append('gambar_ikon', gambarIkonInput.files[0]);
        } else if (id && currentImageInfo.dataset.originalImage && !gambarIkonInput.files.length) {
            // Jika dalam mode edit, tidak ada gambar baru diupload, dan ada gambar sebelumnya,
            // maka kita tidak perlu melakukan apa-apa karena gambar lama akan dipertahankan.
        } else if (id && !currentImageInfo.dataset.originalImage && !gambarIkonInput.files.length) {
            // Jika dalam mode edit, tidak ada gambar baru diupload, dan sebelumnya tidak ada gambar,
            // maka secara eksplisit kirim flag untuk mengosongkan gambar (walaupun sudah kosong).
            formData.append('clear_gambar_ikon', 'true');
        }

        if (id) {
            // Laravel tidak mendukung PUT/PATCH dengan form-data secara langsung untuk file,
            // jadi kita spoof method dengan _method.
            formData.append('_method', 'PUT');
        }

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

        try {
            const response = await fetch(url, {
                method: method,
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json' // Penting untuk memastikan respons JSON
                }
            });

            if (!response.ok) {
                const contentType = response.headers.get("content-type");
                if (contentType && contentType.indexOf("application/json") !== -1) {
                    const errorData = await response.json();
                    let errorMessage = errorData.message || `HTTP error! status: ${response.status}`;
                    if (errorData.errors) {
                        errorMessage += "\n" + Object.values(errorData.errors).flat().join("\n");
                    }
                    throw new Error(errorMessage);
                } else {
                    // Jika respons bukan JSON, asumsikan itu HTML error page
                    // Anda bisa log response.text() untuk melihat HTML-nya
                    const errorText = await response.text();
                    console.error("Server returned non-JSON response:", errorText);
                    // Gunakan Swal.fire untuk error non-JSON
                    Swal.fire({
                        icon: 'error',
                        title: 'Kesalahan Server!',
                        text: `Terjadi kesalahan tak terduga dari server. Status: ${response.status}. Coba lagi nanti.`,
                        footer: '<a href="#" onclick="console.log(decodeURIComponent(this.dataset.responseText)); return false;" data-response-text="' + encodeURIComponent(errorText.substring(0, 5000)) + '">Lihat detail teknis di konsol</a>', // Batasi teks untuk footer
                        showConfirmButton: true
                    });
                    throw new Error(`Server error: Status ${response.status}. Response was not JSON.`);
                }
            }

            const result = await response.json();
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: result.message, // Menggunakan pesan dari respons API
                showConfirmButton: false,
                timer: 1500
            }).then(() => {
                closeModal();
                fetchDataFasilitas(); // Muat ulang data setelah penyimpanan
            });
        } catch (error) {
            console.error('Gagal menyimpan fasilitas:', error);
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: `Gagal menyimpan fasilitas: ${error.message}`,
                showConfirmButton: true
            });
        }
    });

    fetchDataFasilitas(); // Panggil saat halaman pertama dimuat

    // Event listener untuk tombol search
    if (searchButton && searchButton.tagName === 'BUTTON') {
        searchButton.addEventListener('click', () => {
            const searchText = searchInput.value;
            fetchDataFasilitas(searchText);
        });
    }

    // Event listener untuk input search (saat menekan Enter)
    searchInput.addEventListener('keyup', (event) => {
        if (event.key === 'Enter') {
            const searchText = event.target.value;
            fetchDataFasilitas(searchText);
        }
    });
});