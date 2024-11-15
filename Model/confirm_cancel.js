function confirmCancellation() {
    Swal.fire({
      title: "Are you sure?",
      text: "You won't be able to revert this!",
      icon: "warning",
      showCancelButton: true,
      confirmButtonColor: "#3085d6",
      cancelButtonColor: "#d33",
      confirmButtonText: "Yes, cancel my booking!"
    }).then((result) => {
      if (result.isConfirmed) {
        document.getElementById("cancelForm").submit();
        Swal.fire("Cancelled!", "Your booking has been cancelled.", "success");
      }
    });
  }