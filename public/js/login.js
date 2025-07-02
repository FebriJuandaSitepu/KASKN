// // public/js/login-scripts.js

// document.addEventListener('DOMContentLoaded', function() {
//     const errorContainer = document.getElementById('error-messages-data');

//     if (errorContainer && errorContainer.dataset.errors) {
//         const errors = JSON.parse(errorContainer.dataset.errors);

//         let errorMessages = '';
//         for (const key in errors) {
//             errorMessages += errors[key] + '<br>';
//         }

//         // Fungsi untuk mendapatkan variabel CSS (opsional, jika Anda pakai)
//         const getCssVariable = (varName, fallback) => {
//             return getComputedStyle(document.documentElement).getPropertyValue(varName) || fallback;
//         };

//         Swal.fire({
//             icon: 'error',
//             title: 'Login Gagal!',
//             html: errorMessages,
//             confirmButtonText: 'Oke',
//             background: getCssVariable('--card-bg-color', '#2e2e2e'), 
//             color: getCssVariable('--text-color-light', '#f0f0f0'),
//             confirmButtonColor: getCssVariable('--primary-color', '#f5a623')
//         });
//     }
// });

document.addEventListener('DOMContentLoaded', function() {
    const errorContainer = document.getElementById('error-messages-data');

    console.log('--- Debugging Login Pop-up ---'); // Tambahkan ini
    console.log('Error container element:', errorContainer); // Tambahkan ini

    if (errorContainer) {
        console.log('Data errors attribute content:', errorContainer.dataset.errors); // Tambahkan ini

        // Periksa apakah data-errors itu bukan string kosong "[]" yang artinya tidak ada error
        // String JSON yang kosong dari array kosong adalah "[]"
        if (errorContainer.dataset.errors && errorContainer.dataset.errors !== '[]') {
            const errors = JSON.parse(errorContainer.dataset.errors);

            // Pastikan array errors tidak kosong setelah di-parse
            if (errors.length > 0) {
                console.log('Parsed errors:', errors); // Tambahkan ini
                let errorMessages = '';
                for (const key in errors) {
                    errorMessages += errors[key] + '<br>';
                }

                const getCssVariable = (varName, fallback) => {
                    return getComputedStyle(document.documentElement).getPropertyValue(varName) || fallback;
                };

                Swal.fire({
                    icon: 'error',
                    title: 'Login Gagal!',
                    html: errorMessages,
                    confirmButtonText: 'Oke',
                    background: getCssVariable('--card-bg-color', '#2e2e2e'), 
                    color: getCssVariable('--text-color-light', '#f0f0f0'),
                    confirmButtonColor: getCssVariable('--primary-color', '#f5a623')
                });
            } else {
                console.log('No actual errors found (empty array).'); // Tambahkan ini
            }
        } else {
            console.log('No data-errors attribute or it is empty/null.'); // Tambahkan ini
        }
    } else {
        console.log('Error container element not found.'); // Tambahkan ini
    }
    console.log('--- End Debugging Login Pop-up ---'); // Tambahkan ini
});