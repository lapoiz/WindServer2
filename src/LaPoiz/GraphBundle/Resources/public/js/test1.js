
/* Initialise le Graph */
function initGraph() {
    /* init les svg */
    svgGraph=new WindGraph("graphSVG");
    svgLegend=new LegendGraph("legendSVG");
}

/* Use the JSon */
function putOnGraph(jSon) {
    /* Put title name */
    svgGraph.setTitle(jSon.spot);

    /* Put websites on legend */
    svgLegend.addWebSite(jSon.forecast);
}
