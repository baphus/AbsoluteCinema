function showModal(message) {
    const modal = document.getElementById("modal");
    const modalMessage = document.getElementById("modal-message");
    
    modalMessage.textContent = message; // Set the message in the modal
    modal.style.display = "block"; // Show the modal
    }

    function closeModal() {
    const modal = document.getElementById("modal");
    modal.style.display = "none"; // Hide the modal
    }