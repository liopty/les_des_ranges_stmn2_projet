/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you import will output into a single css file (app.css in this case)
import 'bootstrap/dist/css/bootstrap.min.css';
import '../css/app.css';

import $ from 'jquery';


require('@fortawesome/fontawesome-free/css/all.min.css');
require('@fortawesome/fontawesome-free/js/all.js');
require("popper.js");
require("bootstrap");



require('datatables.net');
require('../mdb/js/addons/datatables.min');
import '../mdb/css/addons/datatables.min.css';

require('./datetime-moment.js');
require('../multiselect/js/BsMultiSelect.js');

$(document).ready(function () {
    //ajout du filtre par date aux DataTable
    $.fn.dataTable.moment('DD-MM-YYYY');

    /* ADHERENTS */

    //ouverture formulaire nouvel adherent
    $('#openNewAdherentForm').click(function () {
        //permet de savoir que c'est un nouvel adherent et non un update
        $("#formAdherentAction").val("new");

        //on reset la modale
        $("#form_adherent").find("input[type=text],input[type=email],input[type=date],textarea").val('');
        $("#adherentDatePreCoti").val(new Date().toJSON().slice(0,10));
        $("#adherentDateDerCoti").val(new Date().toJSON().slice(0,10));
        $("#typeAdherent").val("Individuelle");
        $("#persRattaDiv").attr("hidden","hidden");

        //on change le titre de la modale
        $("#modalAdherentTitle").html("Nouvel adhérent");
    });

    //changement type adhesion
    $("#typeAdherent").change(function (){
       if($(this).val() === "Familiale")  $("#persRattaDiv").removeAttr("hidden");
       else  $("#persRattaDiv").attr("hidden","hidden");
    });

    //table adherents
    $('#adherentsTable').DataTable({
        //"scrollX": true,
        "order": [[0, "asc"]],
        "aaSorting": [],
        columnDefs: [{
            orderable: false,
            targets: [10]
        }],
        "lengthMenu": [25, 50, 100],
        "language": {
            "decimal": "",
            "emptyTable": "Aucun élément dans la table",
            "info": "Page _PAGE_ sur _PAGES_",
            "infoEmpty": "Aucun élément à afficher",
            "infoFiltered": "",
            "infoPostFix": "",
            "thousands": ",",
            "lengthMenu": "_MENU_ résultats par page",
            "loadingRecords": "Chargement...",
            "processing": "En cours...",
            "search": "Recherche:",
            "zeroRecords": "Aucun résultat",
            "paginate": {
                "first": "Première",
                "last": "dernière",
                "next": "Suivant",
                "previous": "Précédent"
            },
            "aria": {
                "sortAscending": ": activer pour trier la colonne dans l'ordre croissant",
                "sortDescending": ": activer pour trier la colonne dans l'ordre décroissant"

            }
        }
    });


    /* */


    //affiche spinner chargement sur les envoies de formulaire
    let element = $(this);
    let formId = element.data()['formid'];
    let span =  element.children('span');
    $('#'+formId).submit(function () {
        element.prop('disabled', true);
        span.removeAttr('hidden');
    });

});