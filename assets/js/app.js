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
    if (document.getElementById("adherentPage")) {
    //ouverture formulaire nouvel adherent
        $('#openNewAdherentForm').click(function () {
            //permet de savoir que c'est un nouvel adherent et non un update
            $("#formAdherentAction").val("new");

            //on reset la modale
            $("#form_adherent").find("input[type=text],input[type=email],input[type=date],textarea").val('');
            $("#adherentDatePreCoti").val(new Date().toJSON().slice(0,10));
            $("#adherentDateDerCoti").val(new Date().toJSON().slice(0,10));
            $("#typeAdherent").val("Individuelle").trigger('change');

            //on change le titre de la modale
            $("#modalAdherentTitle").html("Nouvel adhérent");
        });

        //changement type adhesion
        $("#typeAdherent").change(function (){
            if($(this).val() === "Familiale")  $("#persRattaDiv").removeAttr("hidden");
            else  $("#persRattaDiv").attr("hidden","hidden");
        });

        //table adherents
        let adherentTable =$('#adherentsTable').DataTable({
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


        $('#adherentsTable').on('draw.dt', function () {
            $('.openDeleteAdherentForm').off("click");//supprime les anciens scripts si ils existent
            $('.openEditAdherentForm').off("click");

            $('.openEditAdherentForm').click(function (){
                let data = $(this).data();
                $("#formAdherentAction").val("update");
                $("#formAdherentId").val(data["id_adherent"])

                //on change le titre de la modale
                $("#modalAdherentTitle").html("Modification d'adhérent");

                $("#adherentNom").val(data["nom"]);
                $("#adherentPrenom").val(data["prenom"]);

                let dateNaissanceTab = data["date_naissance"].split("-");
                let day = dateNaissanceTab[0];
                let month = dateNaissanceTab[1];
                let year = dateNaissanceTab[2];
                let dateNaissance = year + '-' + month + '-' + day;
                $("#adherentDateNaissance").val(dateNaissance);
                $("#adherentTel").val(data["telephone"]);
                $("#adherentMail").val(data["mail"]);
                let date_premiere_cotisationTab = data["date_premiere_cotisation"].split("-");
                 day = date_premiere_cotisationTab[0];
                 month = date_premiere_cotisationTab[1];
                 year = date_premiere_cotisationTab[2];
                let date_premiere_cotisation = year + '-' + month + '-' + day;
                $("#adherentDatePreCoti").val(date_premiere_cotisation);
                let date_derniere_cotisationTab = data["date_derniere_cotisation"].split("-");
                day = date_derniere_cotisationTab[0];
                month = date_derniere_cotisationTab[1];
                year = date_derniere_cotisationTab[2];
                let date_derniere_cotisation = year + '-' + month + '-' + day;
                $("#adherentDateDerCoti").val(date_derniere_cotisation);
                $("#typeAdherent").val(data["type_adhesion"]).trigger('change');
                $("#persRattaAdherent").val(data["personnes_rattachees"]);
                $("#autreAdherent").val(data["autre"]);

            });

            $(".openDeleteAdherentForm").click(function () {
                let data = $(this).data();
                $('#idAdherent2').val(data["id_adherent"])
            })

        });

        adherentTable.draw();

    }




    /* Jeux */
    /* ADHERENTS */
    if (document.getElementById("jeuPage")) {

        //ouverture formulaire nouveau jeu
        $('#openNewJeuForm').click(function () {
            //permet de savoir que c'est un nouveau jeu et non un update
            $("#formJeuAction").val("new");

            //on reset la modale
            $("#form_jeu").find("input[type=text],input[type=email],input[type=date],textarea").val('');

            //on change le titre de la modale
            $("#modalJeuTitle").html("Nouveau jeu");
        });


        //table adherents
        let jeuTable =$('#jeuxTable').DataTable({
            //"scrollX": true,
            "order": [[0, "asc"]],
            "aaSorting": [],
            columnDefs: [{
                orderable: false,
                targets: [7]
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


        $('#jeuxTable').on('draw.dt', function () {
            $('.openDeleteJeuForm').off("click");//supprime les anciens scripts si ils existent
            $('.openEditJeuForm').off("click");

            $('.openEditJeuForm').click(function (){
                let data = $(this).data();
                $("#formJeuAction").val("update");
                $("#formJeuId").val(data["id_jeu"])

                //on change le titre de la modale
                $("#modalJeuTitle").html("Modification du jeu");

                $("#jeuNom").val(data["nom"]);
                $("#jeuCategorie").val(data["categorie"]);
                $("#jeuEtat").val(data["etat"]);
                $("#jeuDescription").val(data["description"]);

                let isDispo = (data['isdisponible'] === "Oui");
                $("#jeuIsDisponible").prop("checked", isDispo);

                let date_achatTab = data["date_achat"].split("-");
                let day = date_achatTab[0];
                let month = date_achatTab[1];
                let year = date_achatTab[2];
                let date_achat = year + '-' + month + '-' + day;
                $("#jeuDateAchat").val(date_achat);



            });

            $(".openDeleteJeuForm").click(function () {
                let data = $(this).data();
                $('#idJeu2').val(data["id_jeu"])
            })

        });

        jeuTable.draw();

    }



    //affiche spinner chargement sur les envoies de formulaire
    let element = $(this);
    let formId = element.data()['formid'];
    let span =  element.children('span');
    $('#'+formId).submit(function () {
        element.prop('disabled', true);
        span.removeAttr('hidden');
    });

});