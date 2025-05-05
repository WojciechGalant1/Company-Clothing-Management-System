import { GetBaseUrl } from './GetBaseUrl.js';

export const EdycjaUbranie = (() => {
    let ubranieId = null;
    let alertManager = null;

    const initialize = (manager) => {
        alertManager = manager;
        const baseUrl = GetBaseUrl();

        $('#example').on('click', '.open-modal-btn', (event) => {
            const clickedBtn = event.currentTarget;
            ubranieId = $(clickedBtn).data('id');

            const ubraniaData = document.getElementById("ubrania-data");
            if (ubraniaData) {
                const ubrania = JSON.parse(ubraniaData.textContent).map(ubranie => ({
                    ...ubranie,
                    id: parseInt(ubranie.id, 10)
                }));

                const ubranie = ubrania.find(u => u.id === parseInt(ubranieId, 10));
                if (ubranie) {
                    $('#id_ubrania').val(ubranieId);
                    $('#productName').val(ubranie.nazwa_ubrania);
                    $('#sizeName').val(ubranie.nazwa_rozmiaru);
                    $('#ilosc').val(ubranie.ilosc);
                    $('#iloscMin').val(ubranie.iloscMin);

                    $('#editModal').modal('show');
                }
            }
        });

        $('#zapiszUbranie').on('click', (event) => {
            event.preventDefault();

            const form = $('#edycjaUbraniaForm');
            const formData = form.serialize();

            $.ajax({
                url: `${baseUrl}/handlers/updateUbranie.php`,
                type: 'POST',
                data: formData,
                success: (response) => {
                    if (alertManager) {
                        alertManager.createAlert('Edycja zakończona sukcesem', 'success');
                    }
                    $('#editModal').modal('hide');
                    location.reload();
                },
                error: () => {
                    if (alertManager) {
                        alertManager.createAlert('Błąd podczas edycji', 'danger');
                    }
                }
            });
        });
    };

    return { initialize };
})();
