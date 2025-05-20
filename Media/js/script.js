document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('a[data-div]').forEach(link => {
        link.addEventListener('click', function (e) {
            e.preventDefault();

            const targetId = this.getAttribute('data-div');
            const targetDiv = document.getElementById(targetId);

            document.querySelectorAll('[data-div]:not(a)').forEach(div => {
                div.classList.remove('d-block');
                div.classList.add('d-none');
            });

            if (targetDiv) {
                targetDiv.classList.remove('d-none');
                targetDiv.classList.add('d-block');
            }
        });
    });
});
document.addEventListener("DOMContentLoaded", function () {
    const table = document.querySelector(".mytable");  // Usa l'ID o la classe corretta
    const headers = table.querySelectorAll("th");
    const tableBody = table.querySelector("tbody");
    const rows = Array.from(tableBody.querySelectorAll("tr"));

    // Funzione di confronto per l'ordinamento
    const compare = (index, ascending) => (rowA, rowB) => {
        const cellA = rowA.querySelectorAll("td")[index].innerText.toLowerCase();
        const cellB = rowB.querySelectorAll("td")[index].innerText.toLowerCase();

        if (!isNaN(cellA) && !isNaN(cellB)) {
            return ascending ? cellA - cellB : cellB - cellA;
        }

        if (cellA < cellB) {
            return ascending ? -1 : 1;
        }
        if (cellA > cellB) {
            return ascending ? 1 : -1;
        }
        return 0;
    };

    // Funzione per riordinare la tabella
    const sortTable = (index, ascending) => {
        const sortedRows = rows.sort(compare(index, ascending));
        
        // Riordina le righe
        tableBody.replaceChildren(...sortedRows);
    };

    // Aggiunge il listener agli header per rendere le colonne ordinabili
    headers.forEach((header, index) => {
        let ascending = true;
        header.style.cursor = "pointer";  // Aggiungi il cursore a puntatore

        header.addEventListener("click", () => {
            sortTable(index, ascending);
            ascending = !ascending;  // Alterna l'ordinamento
        });
    });
});
