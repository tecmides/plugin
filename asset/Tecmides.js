var Tecmides = (function() {

    function clear(container) {
        while(container.firstChild) {
            container.removeChild(container.firstChild);
        }
    };

    function generateChart(properties) {
        var parent = document.createElement("div");
        parent.id = properties.id;
        parent.classList.add("chart-parent");
        
        for(var propertie in properties.style)
        {
            parent.style[propertie] = properties.style[propertie];
        }
        
        var title = document.createElement("h3");
        title.classList.add("chart-title");
        title.innerText = properties.title;

        var subtitle = document.createElement("h5");
        subtitle.innerText = properties.subtitle;
        subtitle.classList.add("chart-subtitle");

        var chartContainer = document.createElement("div");
        chartContainer.classList.add("chart-container");

        var chart = document.createElement("canvas");
        chart.classList.add("chart");

        parent.appendChild(title);
        parent.appendChild(subtitle);
        parent.appendChild(chartContainer);
        chartContainer.appendChild(chart);

        Chart.defaults.global.defaultFontColor = '#000';

        // Sets up the chart itself
        new Chart(chart, {
            type: properties.type,
            data:
            {
                labels: properties.labels,
                datasets: properties.datasets
            }
        });

        return parent;
    };

    function generateDiscouragedStudentsList(studentList) {
        var container = document.createElement("div");

        var div = document.createElement("div");
        var h3 = document.createElement("h3");
        h3.innerText = studentList.title;

        if(studentList.items.length > 0) {
            studentList.items.forEach(function(item) {
                var p = document.createElement("p");

                p.innerText = item.name + ": " + item.coeficient;

                div.appendChild(p);
            });

            div.classList.add("dangerous");
        }
        else
        {
            var p = document.createElement("p");

            p.innerText = studentList.emptyMessage;
            
            div.appendChild(p);
        }

        container.appendChild(h3);
        container.appendChild(div);
        
        container.style.width = "90%";
        container.style.display = "block";
        container.style.textAlign = "center";
        container.style.margin = "20px auto";
        
        return container;
    };

    return {
        run: function(container, charts, studentList) {
            clear(container);

            charts.forEach(function(item) {
                container.appendChild(generateChart(item));
            });

            container.appendChild(generateDiscouragedStudentsList(studentList));

        }
    };

})();
