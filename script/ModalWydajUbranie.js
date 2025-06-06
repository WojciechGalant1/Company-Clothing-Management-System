import { getBaseUrl } from './utils.js';

export const ModalWydajUbranie = (function () {
    const init = function (alertManager) {
        setupEventListeners(alertManager);
    };

    const setupEventListeners = function (alertManager) {
        const form = document.getElementById('wydajUbranieForm');
        const modal = new bootstrap.Modal(document.getElementById('confirmModal'));
        const confirmButton = document.getElementById('confirmButton');
        const submitBtn = form.querySelector('.submitBtn');
        const loadingSpinner = document.getElementById('loadingSpinner');

        form.addEventListener('custom-submit', (event) => {
            const { success, message } = event.detail;
        
            if (success) {
                if (window.fromRaport) {
                    modal.show();  
                } else {
                    location.reload();
                }
            } else {
                alertManager.createAlert(message || 'Wystąpił błąd podczas przetwarzania żądania.', 'danger');
            }
        });

        confirmButton.addEventListener('click', () => {
            modal.hide();
            const baseUrl = getBaseUrl();
            window.location.href = `${baseUrl}/raport`;
        });
    };

    return { init };
})();
