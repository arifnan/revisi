document.addEventListener('DOMContentLoaded', function () {
    const burger = document.getElementById('burger-menu');

    // Fungsi toggle
    window.toggleSidebar = function () {
        document.body.classList.toggle('sidebar-collapsed');
    };

    // Klik burger
    if (burger) {
        burger.addEventListener('click', toggleSidebar);
    }
    document.addEventListener('DOMContentLoaded', function () {
    window.toggleSidebar = function () {
        document.body.classList.toggle('sidebar-collapsed');
    };
    });

});

function confirmDelete(form) {
    return confirm('Apakah Anda yakin ingin menghapus admin ini?');
}


function confirmDelete(form) {
    return confirm("Yakin ingin menghapus formulir ini? Semua pertanyaan dan jawaban akan ikut terhapus.");
}