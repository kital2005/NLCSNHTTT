<?php
session_start();
// Xóa toàn bộ dữ liệu phiên làm việc
session_unset();
session_destroy();
// Đẩy về trang đăng nhập
header("Location: login.php");
exit();
?>