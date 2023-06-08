$(function () {
    let obrazok =  $("[data-obrazok]");
    obrazok.click(nastavObrazok);
})
function nastavObrazok() {
    let obrazok = $(this).data("obrazok");
    $('.carousel').carousel(obrazok);
}





