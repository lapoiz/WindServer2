// Similaire à LaPoiz\WindBundle\core\nbHoure\MeteoFranceIcon.php

var tabValIcone = null;

function getTabValIcon() {
    if (tabValIcone == null) {
        tabValIcone = new Array;

        var value=0;

        tabValIcone["en"]=value++;  // Ensoleillé - OK
        tabValIcone[""]=value++;    // Ensoleillé nuit - OK
        tabValIcone["c-v"]=value++; // Ciel voilé - OK
        tabValIcone[""]=value++;    // Ciel voilé - OK
        tabValIcone["ec"]=value++;  // Eclaircie - OK
        tabValIcone[""]=value++;    // Eclaircie de nuit - OK

        tabValIcone["tn"]=value;    // tres nuageux - OK
        tabValIcone["t-n"]=value++; // tres nuageux - OK

        tabValIcone["b"]=value++;   // brume - OK
        tabValIcone[""]=value++;    // brume de nuit - OK
        tabValIcone[""]=value++;    // bancs de brouillard
        tabValIcone[""]=value++;    // Brouillard
        tabValIcone[""]=value++;    // Brouillard Givrant

        tabValIcone[""]=value++;   // Bruine / Pluie faible

        tabValIcone[""]=value++;   // Pluie verglaçante
        tabValIcone[""]=value++;   // Pluie verglaçante de nuit
        tabValIcone[""]=value++;   // Pluie verglaçante

        tabValIcone["p-e"]=value++; // Pluies éparses - OK
        tabValIcone[""]=value++;    // Pluies éparses de nuit - OK
        tabValIcone["r-a"]=value++; // Rares averses - OK

        tabValIcone["a"]=value++;   // Averses
        tabValIcone[""]=value++;    // Averse de nuit
        tabValIcone["p"]=value++;   // Pluie - OK

        tabValIcone[""]=value++;    // Pluie forte
        tabValIcone["p-o"]=value++; // Pluie orageuses ?
        tabValIcone[""]=value++;    // Pluie orageuses de nuit - OK
        tabValIcone["a-o"]=value++; // Averses orageuses

        tabValIcone[""]=value++;    //  Quelques flocons
        tabValIcone[""]=value++;    //  Quelques flocons de nuit
        tabValIcone[""]=value++;    //  Quelques flocons

        tabValIcone[""]=value++;    //  neige
        tabValIcone[""]=value++;    //  neige de nuit
        tabValIcone[""]=value++;    //  Averse de neige

        tabValIcone[""]=value++;    //  Neige forte

        tabValIcone["r-g"]=value++; //  Risque de grele
        tabValIcone[""]=value++;    //  Risque de grele de nuit
        tabValIcone[""]=value++;    //  Risque de grele

        tabValIcone["r-o"]=value++; // Risque d'orage
        tabValIcone[""]=value++;    // Risque d'orage de nuit
        tabValIcone[""]=value++;    // Risque d'orage

        tabValIcone["o"]=value++;   // Orages
        tabValIcone[""]=value++;    // Orage la nuit
        tabValIcone[""]=value++;    // Orages


        tabValIcone["?"]=value++;  // Inconnu
        tabValIcone["i"]=value++;  // Iceberck -> inventé

    }
    return tabValIcone;
}