const socket = new WebSocket("ws://localhost:9000");

socket.onopen = function () {
    console.log("Connexion WebSocket établie.");
};

socket.onmessage = function (event) {
    const data = JSON.parse(event.data);
    if (data.type === "notification") {
        afficherNotification(data.message);
    }
};

socket.onerror = function (error) {
    console.error("Erreur WebSocket :", error);
};

socket.onclose = function () {
    console.log("Connexion WebSocket fermée.");
};

function rejoindreWebSocket(patientId) {
    if (socket.readyState === WebSocket.OPEN) {
        socket.send(JSON.stringify({ patientId: patientId }));
    } else {
        socket.addEventListener("open", () => {
            socket.send(JSON.stringify({ patientId: patientId }));
        });
    }
}

// Fonction pour afficher une notification dans l'interface utilisateur
function afficherNotification(message) {
    const notification = document.createElement("div");
    notification.classList.add("notification");
    notification.innerText = message;
    
    document.body.appendChild(notification);

    setTimeout(() => {
        notification.remove();
    }, 5000);
}

// Attendre que le DOM soit chargé avant de récupérer l'ID du patient
document.addEventListener("DOMContentLoaded", function () {
    let patientId = document.body.getAttribute("data-patient-id"); 
    if (patientId) {
        rejoindreWebSocket(patientId);
    }
});
