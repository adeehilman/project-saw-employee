"use strict";
document.addEventListener("DOMContentLoaded", function () {
    document.querySelectorAll("#ya-atau-tidak").forEach(function (elem) {
        elem.addEventListener("click", function (event) {
            event.preventDefault(); // Mencegah perilaku default

            initApp.playSound("/admin/media/sound", "voice_on");

            var title = elem.getAttribute("data-title");
            var message = elem.getAttribute("data-message");
            var secondsLeft = 10; // Waktu dalam detik

            // Fungsi untuk memperbarui tampilan timer
            function updateTimerDisplay() {
                document.getElementById("timer-display").innerText =
                    "Otomatis selama " + secondsLeft + " detik";
                secondsLeft--;

                if (secondsLeft < 0) {
                    clearTimeout(autoRedirectTimer);
                    document.getElementById("logout-form").submit(); // Lakukan perpindahan otomatis di sini jika diperlukan
                }
            }

            // Timer untuk update timer display
            var timerInterval = setInterval(updateTimerDisplay, 1000);

            // Konfigurasi untuk bootbox
            bootbox.confirm({
                title:
                    "<i class='fal fa-sign-out-alt text-warning mr-2'></i> <span class='text-warning fw-300'><strong>" +
                    title +
                    "</strong></span>",
                message:
                    "<span class='fw-900 font-italic'>Pertanyaan: </span><strong>" +
                    message +
                    "</strong><br><br><small class='text-muted'><i class='fal fa-info-circle'></i> Sesi Anda akan berakhir dan Anda akan diarahkan ke halaman utama.</small><br><br> <span class='text-primary font-italic fs-nano' id='timer-display'></span>",
                buttons: {
                    confirm: {
                        label: "Ya",
                        className: "btn-success",
                    },
                    cancel: {
                        label: "Tidak",
                        className: "btn-danger",
                    },
                },
                className: "modal-alert",
                closeButton: false,
                callback: function (result) {
                    clearInterval(timerInterval); // Hentikan update timer
                    clearTimeout(autoRedirectTimer); // Batalkan timer saat tombol diklik
                    if (result) {
                        // Show loading state
                        bootbox.alert({
                            title: "<i class='fal fa-spinner fa-spin text-info mr-2'></i> <span class='text-info'>Logout...</span>",
                            message:
                                "<div class='text-center'><i class='fal fa-spinner fa-spin fa-2x text-info mb-3'></i><br>Sedang memproses logout, mohon tunggu...</div>",
                            closeButton: false,
                            className: "modal-alert",
                        });

                        // Submit form after short delay for better UX
                        setTimeout(function () {
                            document.getElementById("logout-form").submit();
                        }, 1000);
                    } else {
                        console.log("Logout cancelled by user.");
                    }
                },
            });

            // Timer untuk redirect otomatis
            var autoRedirectTimer = setTimeout(function () {
                clearInterval(timerInterval); // Hentikan update timer
                document.getElementById("logout-form").submit(); // Gunakan URL dinamis
            }, secondsLeft * 1000); // Convert detik menjadi milidetik
        });
    });
});
