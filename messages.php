<?php

if (isset($success_msg)) {
    foreach ($success_msg as $success_msgs) {
        echo '<script>swal("' . $success_msgs . '", "", "success");</script>';
    }
}

if (isset($info_msg)) {
    foreach ($info_msg as $info_msgs) {
        echo '<script>swal("' . $info_msgs . '", "", "info");</script>';
    }
}

if (isset($error_msg)) {
    foreach ($error_msg as $error_msgs) {
        echo '<script>swal("' . $error_msgs . '", "", "error");</script>';
    }
}

?>
