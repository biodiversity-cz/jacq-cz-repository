import Plotly from "plotly.js/dist/plotly.js";

export default function drawBoxplot() {

    if (document.getElementById("stats")) {
        init();
    }

}

function init() {

    document.querySelectorAll(".variable").forEach(function (el) {
        const boxplotDiv = el.querySelector(".boxplot");
        const variableName = el.dataset.variable;

        // načteme a parse JSON z data-values
        const variableData = JSON.parse(el.dataset.values);

        const traces = [];

        for (const [institution, values] of Object.entries(variableData)) {
            traces.push({
                type: 'box',
                y: values,
                name: institution
            });
        }

        const layout = {
            title: { text: variableName },
            boxmode: 'group', // boxploty vedle sebe
         };

        Plotly.newPlot(boxplotDiv, traces, layout);
    });
}
