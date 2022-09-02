"use strict";

$(function () {
  getCardChart();
  chart1();
  lineChart();
  dumbbellPlotChart();

  if ($("#project-team-scroll").length) {
    $("#project-team-scroll")
      .css({
        height: 400,
      })
      .niceScroll();
  }
  if ($("#project-list").length) {
    $("#project-list")
      .css({
        height: 400,
      })
      .niceScroll();
  }
  if ($("#client-details").length) {
    $("#client-details")
      .css({
        height: 400,
      })
      .niceScroll();
  }
});

function getCardChart() {
  var randomFromArray = function (array) {
    var currentIndex = array.length,
      temporaryValue,
      randomIndex;

    // While there remain elements to shuffle...
    while (0 !== currentIndex) {
      // Pick a remaining element...
      randomIndex = Math.floor(Math.random() * currentIndex);
      currentIndex -= 1;

      // And swap it with the current element.
      temporaryValue = array[currentIndex];
      array[currentIndex] = array[randomIndex];
      array[randomIndex] = temporaryValue;
    }

    return array;
  };

  var chartData = [47, 29, 54, 34, 41, 22, 48, 53, 22, 20, 50, 59, 56, 45, 48];

  // chart 1
  var card1 = {
    chart: {
      type: "area",
      height: 50,
      sparkline: {
        enabled: true,
      },
    },
    stroke: {
      width: 2,
    },
    series: [
      {
        data: randomFromArray(chartData),
      },
    ],
    colors: ["#FA9313"],
    tooltip: {
      theme: "dark",
      marker: {
        show: true,
      },
      x: {
        show: false,
      },
    },
  };
  var card1 = new ApexCharts(document.querySelector("#cardChart1"), card1);
  card1.render();

  // chart 2
  var card2 = {
    chart: {
      type: "area",
      height: 50,
      sparkline: {
        enabled: true,
      },
    },
    stroke: {
      width: 2,
    },
    series: [
      {
        data: randomFromArray(chartData),
      },
    ],
    colors: ["#99C853"],
    tooltip: {
      theme: "dark",
      marker: {
        show: true,
      },
      x: {
        show: false,
      },
    },
  };
  var card2 = new ApexCharts(document.querySelector("#cardChart2"), card2);
  card2.render();

  // chart 3
  var card3 = {
    chart: {
      type: "area",
      height: 50,
      sparkline: {
        enabled: true,
      },
    },
    stroke: {
      width: 2,
    },
    series: [
      {
        data: randomFromArray(chartData),
      },
    ],
    colors: ["#1B55E2"],
    tooltip: {
      theme: "dark",
      marker: {
        show: true,
      },
      x: {
        show: false,
      },
    },
  };
  var card3 = new ApexCharts(document.querySelector("#cardChart3"), card3);
  card3.render();

  // chart 4
  var card4 = {
    chart: {
      type: "area",
      height: 50,
      sparkline: {
        enabled: true,
      },
    },
    stroke: {
      width: 2,
    },
    series: [
      {
        data: randomFromArray(chartData),
      },
    ],
    colors: ["#E7515A"],
    tooltip: {
      theme: "dark",
      marker: {
        show: true,
      },
      x: {
        show: false,
      },
    },
  };
  var card4 = new ApexCharts(document.querySelector("#cardChart4"), card4);
  card4.render();
}

function chart1() {
  var options = {
    series: [
      {
        name: "series1",
        data: [81, 90, 78, 101, 92, 109, 100],
      },
      {
        name: "series2",
        data: [61, 82, 95, 82, 84, 102, 91],
      },
    ],
    chart: {
      height: 380,
      type: "area",
      dropShadow: {
        enabled: true,
        opacity: 0.3,
        blur: 5,
        left: -7,
        top: 22,
      },
      toolbar: {
        show: false,
      },
    },
    colors: ["#6777EF", "#FEB019"],
    dataLabels: {
      enabled: false,
    },
    stroke: {
      show: true,
      curve: "smooth",
      width: 3,
      lineCap: "square",
    },
    xaxis: {
      axisBorder: {
        show: false,
      },
      axisTicks: {
        show: false,
      },
      crosshairs: {
        show: true,
      },
      categories: ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug"],
      labels: {
        offsetX: 0,
        offsetY: 5,
        style: {
          fontSize: "12px",
          fontFamily: "Segoe UI",
          cssClass: "apexcharts-xaxis-title",
        },
      },
    },
    yaxis: {
      labels: {
        offsetX: 0,
        offsetY: 0,
        style: {
          color: "#8e8da4",
        },
      },
    },
    legend: {
      show: false,
    },
    tooltip: {
      theme: "dark",
      marker: {
        show: true,
      },
      x: {
        show: true,
      },
    },
  };

  var chart = new ApexCharts(document.querySelector("#chart1"), options);
  chart.render();

  $('#chart-tabs a[data-toggle="tab"]').on("shown.bs.tab", function (e) {
    var id = $(this).attr("data-id");
    if (id && id === "1") {
      chart.updateSeries([
        {
          name: "income",
          data: [15, 48, 36, 20, 40, 60, 35, 20],
        },
        {
          name: "expense",
          data: [8, 22, 60, 35, 17, 24, 48, 37],
        },
      ]);
    } else if (id && id === "2") {
      chart.updateSeries([
        {
          name: "income",
          data: [19, 38, 43, 27, 44, 55, 32, 26],
        },
        {
          name: "expense",
          data: [12, 20, 58, 39, 21, 31, 41, 37],
        },
      ]);
    } else if (id && id === "3") {
      chart.updateSeries([
        {
          name: "income",
          data: [10, 28, 22, 32, 41, 51, 42, 30],
        },
        {
          name: "expense",
          data: [17, 22, 42, 35, 31, 28, 53, 31],
        },
      ]);
    }
  });
}
function lineChart() {
  am4core.useTheme(am4themes_animated);

  // Create chart instance
  var chart = am4core.create("amchartLineDashboard", am4charts.XYChart);

  // Add data
  chart.data = [
    {
      year: "1983",
      value: 0.177,
    },
    {
      year: "1984",
      value: -0.021,
    },
    {
      year: "1985",
      value: -0.037,
    },
    {
      year: "1986",
      value: 0.03,
    },
    {
      year: "1987",
      value: 0.179,
    },
    {
      year: "1988",
      value: 0.18,
    },
    {
      year: "1989",
      value: 0.104,
    },
    {
      year: "1990",
      value: 0.255,
    },
    {
      year: "1991",
      value: 0.21,
    },
    {
      year: "1992",
      value: 0.065,
    },
    {
      year: "1993",
      value: 0.11,
    },
    {
      year: "1994",
      value: 0.172,
    },
    {
      year: "1995",
      value: 0.269,
    },
    {
      year: "1996",
      value: 0.141,
    },
    {
      year: "1997",
      value: 0.353,
    },
    {
      year: "1998",
      value: 0.548,
    },
    {
      year: "1999",
      value: 0.298,
    },
    {
      year: "2000",
      value: 0.267,
    },
    {
      year: "2001",
      value: 0.411,
    },
    {
      year: "2002",
      value: 0.462,
    },
    {
      year: "2003",
      value: 0.47,
    },
    {
      year: "2004",
      value: 0.445,
    },
    {
      year: "2005",
      value: 0.47,
    },
  ];
  // Create axes
  var dateAxis = chart.xAxes.push(new am4charts.DateAxis());
  dateAxis.renderer.grid.template.location = 0;
  dateAxis.renderer.labels.template.fill = am4core.color("#9aa0ac");
  //dateAxis.renderer.minGridDistance = 30;

  var valueAxis = chart.yAxes.push(new am4charts.ValueAxis());
  valueAxis.renderer.labels.template.fill = am4core.color("#9aa0ac");

  // Create series
  function createSeries(field, name, date) {
    var series = chart.series.push(new am4charts.LineSeries());
    series.dataFields.valueY = field;
    series.dataFields.dateX = "year";
    series.name = name;
    series.strokeWidth = 2;

    var bullet = series.bullets.push(new am4charts.CircleBullet());
    bullet.circle.stroke = am4core.color("#fff");
    bullet.circle.strokeWidth = 2;
  }

  createSeries("value", "Series #1", "date");

  chart.legend = new am4charts.Legend();
  chart.cursor = new am4charts.XYCursor();
}

function dumbbellPlotChart() {
  // Themes begin
  am4core.useTheme(am4themes_animated);
  // Themes end

  var chart = am4core.create("dumbbellPlotChart", am4charts.XYChart);

  var data = [];
  var open = 100;
  var close = 120;

  var names = [
    "Raina",
    "Demarcus",
    "Carlo",
    "Jacinda",
    "Richie",
    "Antony",
    "Amada",
    "Idalia",
    "Janella",
    "Marla",
    "Curtis",
    "Shellie",
  ];

  for (var i = 0; i < names.length; i++) {
    open += Math.round((Math.random() < 0.5 ? 1 : -1) * Math.random() * 5);
    close = open + Math.round(Math.random() * 10) + 3;
    data.push({
      category: names[i],
      open: open,
      close: close,
    });
  }

  chart.data = data;

  var categoryAxis = chart.xAxes.push(new am4charts.CategoryAxis());
  categoryAxis.renderer.grid.template.location = 0;
  categoryAxis.renderer.ticks.template.disabled = true;
  categoryAxis.renderer.axisFills.template.disabled = true;
  categoryAxis.dataFields.category = "category";
  categoryAxis.renderer.minGridDistance = 15;
  categoryAxis.renderer.grid.template.location = 0.5;
  categoryAxis.renderer.grid.template.strokeDasharray = "1,3";
  categoryAxis.renderer.labels.template.rotation = -90;
  categoryAxis.renderer.labels.template.horizontalCenter = "left";
  categoryAxis.renderer.labels.template.dx = 17;
  categoryAxis.renderer.inside = true;
  categoryAxis.renderer.labels.template.fill = am4core.color("#9aa0ac");

  var valueAxis = chart.yAxes.push(new am4charts.ValueAxis());
  valueAxis.tooltip.disabled = true;
  valueAxis.renderer.ticks.template.disabled = true;
  valueAxis.renderer.axisFills.template.disabled = true;
  valueAxis.renderer.labels.template.fill = am4core.color("#9aa0ac");

  var series = chart.series.push(new am4charts.ColumnSeries());
  series.dataFields.categoryX = "category";
  series.dataFields.openValueY = "open";
  series.dataFields.valueY = "close";
  series.tooltipText = "open: {openValueY.value} close: {valueY.value}";
  series.sequencedInterpolation = true;
  series.fillOpacity = 0;
  series.strokeOpacity = 1;
  series.columns.template.width = 0.01;
  series.tooltip.pointerOrientation = "horizontal";

  var openBullet = series.bullets.create(am4charts.CircleBullet);
  openBullet.locationY = 1;

  var closeBullet = series.bullets.create(am4charts.CircleBullet);

  closeBullet.fill = chart.colors.getIndex(4);
  closeBullet.stroke = closeBullet.fill;

  chart.cursor = new am4charts.XYCursor();
}
