/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you import will output into a single css file (app.css in this case)

import('select2/dist/css/select2.min.css');
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

require('select2/dist/js/select2.min')

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
            $("#adherentDatePreCoti").val(new Date().toJSON().slice(0, 10));
            $("#adherentDateDerCoti").val(new Date().toJSON().slice(0, 10));
            $("#typeAdherent").val("Individuelle").trigger('change');

            //on change le titre de la modale
            $("#modalAdherentTitle").html("Nouvel adhérent");
        });

        //changement type adhesion
        $("#typeAdherent").change(function () {
            if ($(this).val() === "Familiale") $("#persRattaDiv").removeAttr("hidden");
            else $("#persRattaDiv").attr("hidden", "hidden");
        });

        //table adherents
        let adherentTable = $('#adherentsTable').DataTable({
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

            $('.openEditAdherentForm').click(function () {
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
        let jeuTable = $('#jeuxTable').DataTable({
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

            $('.openEditJeuForm').click(function () {
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

    /* Stocks */
    if (document.getElementById("consommablesPage")) {

        $('#openNewConsommablesForm').click(function () {
            $("#formConsommablesAction").val("new");
            $("#form_consommables").find("input[type=text],input[type=email],input[type=date],textarea").val('');

            $("#consommablesPrix_unitaire").val(0.00);
            $("#consommablesQte").val(0);

            //on change le titre de la modale
            $("#modalConsommablesTitle").html("Nouveau produit");
        });


        //table adherents
        let produitTable = $('#consommablesTable').DataTable({
            //"scrollX": true,
            "order": [[0, "asc"]],
            "aaSorting": [],
            columnDefs: [{
                orderable: false,
                targets: [3]
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


        $('#consommablesTable').on('draw.dt', function () {
            $('.openDeleteConsommablesForm').off("click");//supprime les anciens scripts si ils existent
            $('.openEditConsommablesForm').off("click");

            $('.openEditConsommablesForm').click(function () {
                let data = $(this).data();
                $("#formConsommablesAction").val("update");
                $("#formConsommablesId").val(data["id_consommable"])

                //on change le titre de la modale
                $("#modalConsommablesTitle").html("Modification du produit");

                $("#consommablesLabel").val(data["label"]);
                $("#consommablesPrix_unitaire").val(data["prix_unitaire"]);
                $("#consommablesQte").val(data["qte"]);

            });

            $(".openDeleteConsommablesForm").click(function () {
                let data = $(this).data();
                $('#idConsommables2').val(data["id_consommable"])
            })

        });

        produitTable.draw();

    }
    /* Emprunts */
    if (document.getElementById("empruntPage")) {

        $('#emprunt_adherent').select2();
        $('#emprunt_jeu').select2();


        $('#openNewEmpruntForm').click(function () {
            $("#formEmpruntsAction").val("new");

            $("#emprunt_dateemprunt").val(new Date().toJSON().slice(0, 10));
            let date7 = new Date();
            date7.setDate(date7.getDate() + 7);
            $("#emprunt_dateretourprevu").val(date7.toJSON().slice(0, 10));

            $("#emprunt_adherent").val("").trigger('change');
            $("#emprunt_jeu").val("").trigger('change');

            //on change le titre de la modale
            $("#modalEmpruntTitle").html("Nouvel emprunt");
        });


        //table adherents
        let empruntTable = $('#empruntsTable').DataTable({
            //"scrollX": true,
            "order": [[3, "asc"]],
            "aaSorting": [],
            columnDefs: [{
                orderable: false,
                targets: [5]
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


        $('#empruntsTable').on('draw.dt', function () {
            $('.openDeleteEmpruntsForm').off("click");//supprime les anciens scripts si ils existent
            $('.openEditEmpruntForm').off("click");
            $(".openRendreEmpruntForm").off("click");

            $('.openEditEmpruntForm').click(function () {
                let data = $(this).data();
                $("#formEmpruntsAction").val("update");
                $("#formEmpruntId").val(data["id_emprunt"])

                //on change le titre de la modale
                $("#modalEmpruntTitle").html("Modification de l'emprunt");

                let dateempruntTab = data["date_emprunt"].split("-");
                let day = dateempruntTab[0];
                let month = dateempruntTab[1];
                let year = dateempruntTab[2];
                let dateemprunt = year + '-' + month + '-' + day;
                $("#emprunt_dateemprunt").val(dateemprunt);
                let dateretourprevTab = data["date_retourprevu"].split("-");
                day = dateretourprevTab[0];
                month = dateretourprevTab[1];
                year = dateretourprevTab[2];
                let dateretourprev = year + '-' + month + '-' + day;
                $("#emprunt_dateretourprevu").val(dateretourprev);

                $("#emprunt_adherent").val(data["id_adherent"]).trigger('change');
                $("#emprunt_jeu").val(data["id_jeu"]).trigger('change');

            });

            $(".openDeleteEmpruntsForm").click(function () {
                let data = $(this).data();
                $('#formEmpruntId2').val(data["id_emprunt"])
            })

            $(".openRendreEmpruntForm").click(function () {
                let data = $(this).data();
                $("#emprunt_dateretour").val(new Date().toJSON().slice(0, 10));
                $('#formEmpruntId3').val(data["id_emprunt"])
            });

        });


        $.fn.dataTable.ext.search.push(
            function (settings, data, dataIndex) {
                let retour = $("#affichage_emprunts").val();
                let colretour = data[4] || "";

                return (retour === "all")
                    || (retour === "encours" && colretour === "")
                    || (retour === "archives" && colretour !== "");
            }
        );

        $("#affichage_emprunts").change(function () {
            empruntTable.draw();

            let val = $(this).val();
            switch (val) {
                case "all":
                    $(".toHideEncours").removeAttr("hidden");
                    break;
                case "encours":
                    $(".toHideEncours").attr("hidden", "hidden");
                    break;
                case "archives":
                    $(".toHideEncours").removeAttr("hidden");
                    break;
                default:
                    break;
            }

        });


        $("#affichage_emprunts").val("encours").change();


    }


    /* Ventes */
    if (document.getElementById("ventePage")) {

        $('#vente_adherent').select2();


        $('#openNewVenteForm').click(function () {
            $("#formVentesAction").val("new");

            $("#modalVenteTitle").html("Nouvelle vente")


            $(".produitselect").slice(1).remove();
            $("#formVentesNbProduits").val(0);


            $("#vente_adherent").val("").trigger('change');
            $("#vente_produit0").val("").trigger('change');
            $("#vente_quantite0").val("");

        });


        //table adherents
        let venteTable = $('#ventesTable').DataTable({
            //"scrollX": true,
            "order": [[0, "desc"]],
            "aaSorting": [],
            columnDefs: [{
                orderable: false,
                targets: [3]
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


        $('#ventesTable').on('draw.dt', function () {
            $('.openInfosVenteForm').off("click");

            $('.openInfosVenteForm').click(function () {
                let data = $(this).data();

                let url = data["url"];

                let spin = $("#infosSpinner");
                //affiche chargement
                spin.removeAttr("hidden");
                $("#infosventecontainer").attr("hidden", "hidden");
                $.ajax({
                    type: 'post',
                    url: url,
                    data: {
                        token: "appRequest"
                    },
                    success: function (res) {
                        $("#infosventecontainer").html(res).removeAttr("hidden");
                        spin.attr("hidden", "hidden");
                    }
                });


            });

        });


        venteTable.draw();


        $('select.selectProduit').select2();

        $('#addProduitbtn').click(function () {

            $('select.selectProduit').select2("destroy");

            let noOfDivs = $('.produitselect').length;
            let clonedDiv = $('.produitselect').first().clone(true);
            $("#produitsContainer").append(clonedDiv);

            clonedDiv.attr('id', 'produitselect' + noOfDivs);

            $("#produitselect" + (noOfDivs - 1)).removeClass("islast");
            clonedDiv.addClass("islast");
            $(".islast .labelproduit").attr("for", "vente_produit" + noOfDivs);
            $(".islast .selectProduit").attr("id", "vente_produit" + noOfDivs);
            $(".islast .selectProduit").attr("name", "produit" + noOfDivs);
            $(".islast .selectProduit").removeAttr("required");

            $(".islast .labelquantite").attr("for", "vente_quantite" + noOfDivs);
            $(".islast .vente_quantite").attr("id", "vente_quantite" + noOfDivs);
            $(".islast .vente_quantite").attr("name", "quantite" + noOfDivs);
            $(".islast .vente_quantite").val(0);
            $(".islast .vente_quantite").removeAttr("required");


            $('select.selectProduit').select2();

            $('#formVentesNbProduits').val(noOfDivs);
        });

        $(".selectProduit").change(function (){
            let num = $(this).attr("id").split("vente_produit")[1];
            let stock = $(this).find('option:selected').text().split('stock: ').pop().split(')')[0];
            if(stock >= 0) $("#vente_quantite"+num).attr("max",stock);
        });

    }

    //affiche spinner chargement sur les envoies de formulaire
    $('.submitForm').click(function () {
        let element = $(this);
        let formId = element.data()['formid'];
        let span = element.children('span');
        $('#' + formId).submit(function () {
            element.prop('disabled', true);
            span.removeAttr('hidden');
        });
    })


    if ($("#errormsg").html() !== "true" && $("#errormsg").html() !== "" && $("#errormsg").html() !== "1" && $("#errormsg").html() !== null && $("#errormsg").html() !== "NULL" && $("#errormsg").html() !== "null") {
        $("#errormsgContainer").removeAttr("hidden");
        setTimeout(function () {
            $("#errormsgContainer").attr("hidden", "hidden");
        }, 20000)
    }

});


