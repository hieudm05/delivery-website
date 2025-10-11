@if(session('success'))
<script>
if (window.performance && window.performance.navigation.type === 2) {
    // Nếu là back/forward, không hiển thị lại alert
} else {
    Swal.fire({
      title: "Thành công!",
      text: "{{ session('success') }}",
      icon: "success"
    }).then(() => {
        if (window.history.replaceState) {
            window.history.replaceState(null, null, window.location.href);
        }
    });
}
</script>
@endif

@if(session('error'))
<script>
if (window.performance && window.performance.navigation.type === 2) {
    // Nếu là back/forward, không hiển thị lại alert
} else {
    Swal.fire({
      title: "Lỗi!",
      text: "{{ session('error') }}",
      icon: "error"
    }).then(() => {
        if (window.history.replaceState) {
            window.history.replaceState(null, null, window.location.href);
        }
    });
}
</script>
@endif