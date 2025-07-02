// Futsal/pemesanan.js

document.addEventListener('DOMContentLoaded', () => {
    // Pastikan elemen-elemen ini ada di pemesanan.blade.php Anda
    const dataTableBody = document.querySelector('.data-table tbody'); // Ambil tbody dari tabel pemesanan
    const searchInput = document.getElementById('search-lapangan'); // ID input search di pemesanan.blade.php
    const searchButton = searchInput ? searchInput.nextElementSibling : null; // Tombol search di samping input

    // Fungsi untuk format Rupiah (jika diperlukan untuk menampilkan harga)
    function formatRupiah(angka) {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0
        }).format(angka);
    }

    // Fungsi untuk merender data ke dalam tabel
    function renderTableData(data) {
        if (!dataTableBody) {
            console.error("Elemen <tbody> tabel pemesanan tidak ditemukan.");
            return;
        }
        dataTableBody.innerHTML = ''; // Kosongkan isi tabel sebelumnya

        console.log("Memulai proses render tabel pemesanan dengan data:", data);

        if (data.length === 0) {
            dataTableBody.innerHTML = '<tr><td colspan="8" style="text-align: center;">Tidak ada data pemesanan.</td></tr>';
            console.log("Tidak ada data pemesanan untuk ditampilkan.");
            return;
        }

        data.forEach(pemesanan => {
            console.log("Memproses pemesanan:", pemesanan.id_pemesanan, pemesanan.konsumen_id); // Perbaikan: Gunakan id_pemesanan

            const row = document.createElement('tr');

            const tdId = document.createElement('td');
            tdId.textContent = pemesanan.id_pemesanan; // Perbaikan: Gunakan id_pemesanan
            row.appendChild(tdId);

            const tdUserId = document.createElement('td');
            tdUserId.textContent = pemesanan.konsumen_id; // Menggunakan konsumen_id
            row.appendChild(tdUserId);

            const tdLapanganId = document.createElement('td');
            tdLapanganId.textContent = pemesanan.lapangan_id;
            row.appendChild(tdLapanganId);

            const tdTanggal = document.createElement('td');
            tdTanggal.textContent = pemesanan.tanggal;
            row.appendChild(tdTanggal);

            const tdJamMulai = document.createElement('td');
            tdJamMulai.textContent = pemesanan.jam_mulai;
            row.appendChild(tdJamMulai);

            const tdJamSelesai = document.createElement('td');
            tdJamSelesai.textContent = pemesanan.jam_selesai;
            row.appendChild(tdJamSelesai);

            const tdHarga = document.createElement('td');
            tdHarga.textContent = formatRupiah(pemesanan.harga);
            row.appendChild(tdHarga);

            const tdCatatan = document.createElement('td');
            tdCatatan.textContent = pemesanan.catatan ?? '-'; // Tampilkan catatan atau '-' jika null
            row.appendChild(tdCatatan);

            dataTableBody.appendChild(row);
        });
        console.log("Selesai merender data pemesanan.");
    }

    // Fungsi untuk mengambil data pemesanan dari API
    async function fetchDataPemesanan(searchTerm = '', page = 1) { // Tambahkan parameter page
        try {
            console.log("Fetching data pemesanan...");
            let url = `/api/pemesanan?page=${page}`; // Endpoint API pemesanan (sama dengan yang di Postman)
            if (searchTerm) {
                // Di controller, pencarian dilakukan berdasarkan 'id' atau 'konsumen_id'
                url += `&search=${encodeURIComponent(searchTerm)}`; // Tambahkan parameter search
            }

            const response = await fetch(url, {
                headers: {
                    'Accept': 'application/json' // Penting untuk memastikan respons JSON
                }
            });

            if (!response.ok) {
                const errorData = await response.json();
                throw new Error(errorData.message || `HTTP error! status: ${response.status}`);
            }

            const result = await response.json(); // Laravel paginate mengembalikan objek dengan 'data'
            const data = result.data; // Ambil array data dari objek pagination

            console.log("Data pemesanan berhasil diambil:", data);
            renderTableData(data); // Kirim data ke fungsi render

            // --- Bagian untuk mengelola Pagination Link ---
            const paginationSection = document.querySelector('.pagination-section .pagination');
            if (paginationSection) {
                paginationSection.innerHTML = ''; // Kosongkan paginasi lama

                // Buat link "Previous"
                const prevLi = document.createElement('li');
                prevLi.classList.add('page-item');
                if (result.prev_page_url === null) {
                    prevLi.classList.add('disabled');
                }
                const prevLink = document.createElement('a');
                prevLink.classList.add('page-link');
                prevLink.href = result.prev_page_url || '#';
                prevLink.textContent = 'Previous';
                prevLink.addEventListener('click', (e) => {
                    e.preventDefault();
                    if (result.prev_page_url) {
                        const newPage = new URL(result.prev_page_url).searchParams.get('page');
                        fetchDataPemesanan(searchInput.value, newPage); // Gunakan newPage
                    }
                });
                prevLi.appendChild(prevLink);
                paginationSection.appendChild(prevLi);

                // Buat link nomor halaman
                result.links.forEach(link => {
                    if (link.url === null || (link.label === '&laquo; Previous' || link.label === 'Next &raquo;')) {
                        return; // Skip default prev/next links as we handle them separately
                    }

                    const pageLi = document.createElement('li');
                    pageLi.classList.add('page-item');
                    if (link.active) {
                        pageLi.classList.add('active');
                    }
                    const pageLink = document.createElement('a');
                    pageLink.classList.add('page-link');
                    pageLink.href = link.url || '#';
                    pageLink.textContent = link.label;
                    pageLink.addEventListener('click', (e) => {
                        e.preventDefault();
                        if (link.url) {
                            const newPage = new URL(link.url).searchParams.get('page');
                            fetchDataPemesanan(searchInput.value, newPage); // Gunakan newPage
                        }
                    });
                    pageLi.appendChild(pageLink);
                    paginationSection.appendChild(pageLi);
                });

                // Buat link "Next"
                const nextLi = document.createElement('li');
                nextLi.classList.add('page-item');
                if (result.next_page_url === null) {
                    nextLi.classList.add('disabled');
                }
                const nextLink = document.createElement('a');
                nextLink.classList.add('page-link');
                nextLink.href = result.next_page_url || '#';
                nextLink.textContent = 'Next';
                nextLink.addEventListener('click', (e) => {
                    e.preventDefault();
                    if (result.next_page_url) {
                        const newPage = new URL(result.next_page_url).searchParams.get('page');
                        fetchDataPemesanan(searchInput.value, newPage); // Gunakan newPage
                    }
                });
                nextLi.appendChild(nextLink);
                paginationSection.appendChild(nextLi);
            }


        } catch (error) {
            console.error("Gagal mengambil data pemesanan:", error);
            if (dataTableBody) {
                dataTableBody.innerHTML = '<tr><td colspan="8" style="text-align: center; color: red;">Gagal memuat data. Silakan coba lagi.</td></tr>';
            }
            // SweetAlert2 (pastikan Anda sudah menyertakan SweetAlert2 di layout utama)
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal Memuat Data!',
                    text: `Terjadi kesalahan saat memuat data pemesanan: ${error.message}`,
                    showConfirmButton: true
                });
            } else {
                alert(`Gagal memuat data pemesanan: ${error.message}`);
            }
        }
    }

    // Event listener untuk tombol search
    if (searchButton) { // Pastikan tombol search ada
        searchButton.addEventListener('click', () => {
            const searchTerm = searchInput.value;
            fetchDataPemesanan(searchTerm, 1); // Reset ke halaman 1 saat search
        });
    }

    // Event listener untuk input search (saat menekan Enter)
    if (searchInput) { // Pastikan input search ada
        searchInput.addEventListener('keyup', (event) => {
            if (event.key === 'Enter') {
                const searchTerm = searchInput.value;
                fetchDataPemesanan(searchTerm, 1); // Reset ke halaman 1 saat search
            }
        });
    }

    // Panggil fetchDataPemesanan saat halaman dimuat untuk pertama kali
    fetchDataPemesanan();
});