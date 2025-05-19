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
