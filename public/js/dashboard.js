// public/js/dashboard.js

document.addEventListener('DOMContentLoaded', function() {
    const sidebarNavItems = document.querySelectorAll('.sidebar-nav ul li');

    function updateActiveMenuItem(targetItem) {
        sidebarNavItems.forEach(item => {
            item.classList.remove('active');
        });

        if (targetItem) {
            targetItem.classList.add('active');
            localStorage.setItem('activeMenuItemId', targetItem.id);
            console.log(`[JS DEBUG] Setting active item: ${targetItem.id}`); // DEBUG
        } else {
            localStorage.removeItem('activeMenuItemId');
            console.log('[JS DEBUG] No target item, clearing localStorage active ID.'); // DEBUG
        }
    }

    // --- Logika Penentuan Item Aktif saat Halaman Dimuat ---
    let currentPath = window.location.pathname;
    if (currentPath.length > 1 && currentPath.endsWith('/')) {
        currentPath = currentPath.slice(0, -1);
    }
    console.log(`[JS DEBUG] Current URL Path (normalized): '${currentPath}'`); // DEBUG

    let activeItemFoundByUrl = false;

    sidebarNavItems.forEach(item => {
        const link = item.querySelector('a');
        if (link && link.href) {
            const linkUrl = new URL(link.href);
            let linkPath = linkUrl.pathname;

            if (linkPath.length > 1 && linkPath.endsWith('/')) {
                linkPath = linkPath.slice(0, -1);
            }
            
            console.log(`[JS DEBUG] Comparing currentPath '${currentPath}' with linkPath '${linkPath}' for item ID '${item.id}'`); // DEBUG

            if (currentPath === linkPath) {
                updateActiveMenuItem(item);
                activeItemFoundByUrl = true;
                console.log(`[JS DEBUG] *** MATCH FOUND by URL for item ID: '${item.id}' ***`); // DEBUG
            }
        }
    });

    // --- Logika Fallback ---
    if (!activeItemFoundByUrl) {
        console.log('[JS DEBUG] No URL match found. Checking localStorage/default.'); // DEBUG
        const savedActiveMenuItemId = localStorage.getItem('activeMenuItemId');
        
        if (savedActiveMenuItemId) {
            console.log(`[JS DEBUG] localStorage has saved ID: '${savedActiveMenuItemId}'`); // DEBUG
            const savedActiveItem = document.getElementById(savedActiveMenuItemId);
            if (savedActiveItem) {
                updateActiveMenuItem(savedActiveItem);
                console.log(`[JS DEBUG] Fallback: Activating item from localStorage: ${savedActiveMenuItemId}`); // DEBUG
            } else {
                localStorage.removeItem('activeMenuItemId');
                console.log('[JS DEBUG] Fallback: localStorage ID not found in DOM, removed.'); // DEBUG
                const defaultActiveItem = document.getElementById('menu-dashboard');
                if (defaultActiveItem) {
                    updateActiveMenuItem(defaultActiveItem);
                    console.log('[JS DEBUG] Fallback: Defaulting to Dashboard (localStorage ID not found).'); // DEBUG
                }
            }
        } else {
            console.log('[JS DEBUG] No localStorage ID found. Defaulting to Dashboard.'); // DEBUG
            const defaultActiveItem = document.getElementById('menu-dashboard');
            if (defaultActiveItem) {
                updateActiveMenuItem(defaultActiveItem);
            }
        }
    } else {
        console.log('[JS DEBUG] URL match found, localStorage/default logic skipped.'); // DEBUG
    }

    // --- Event Listener untuk Klik pada Item Menu ---
    sidebarNavItems.forEach(item => {
        item.addEventListener('click', function(event) {
            // Ini akan memberikan feedback visual instan sebelum halaman dimuat ulang.
            // Logika di atas (DOMContentLoaded) akan mengatur class aktif secara akurat
            // setelah halaman baru dimuat berdasarkan URL.
            updateActiveMenuItem(this);
            console.log(`[JS DEBUG] Menu item clicked: ${this.id}`); // DEBUG
        });
    });
});