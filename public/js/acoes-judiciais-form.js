// public/js/acoes-judiciais-form.js
document.addEventListener('DOMContentLoaded', function () {
    var imovelCheckbox      = document.getElementById('imovel_devolvido');
    var blocoDevolucao      = document.getElementById('bloco_devolucao_imovel');
    var acordoCheckbox      = document.getElementById('houve_acordo');
    var blocoAcordo         = document.getElementById('bloco_acordo');

    function toggleDevolucao() {
        blocoDevolucao.style.display = imovelCheckbox.checked ? 'block' : 'none';
    }

    function toggleAcordo() {
        blocoAcordo.style.display = acordoCheckbox.checked ? 'block' : 'none';
    }

    imovelCheckbox.addEventListener('change', toggleDevolucao);
    acordoCheckbox.addEventListener('change', toggleAcordo);

    // Aplica o estado inicial correto (respeita old() já no HTML)
    toggleDevolucao();
    toggleAcordo();
});