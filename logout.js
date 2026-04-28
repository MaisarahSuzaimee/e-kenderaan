function handleLogout() {
    Swal.fire({
        title: 'Log Keluar?',
        text: "Anda pasti mahu log keluar dari sistem?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Log Keluar',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            // Redirect to logout file
            window.location.href = 'logout.php';
        }
    });
}
