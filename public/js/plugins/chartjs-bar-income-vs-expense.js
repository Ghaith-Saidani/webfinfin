function updateChartWithNewData(chart, newData) {
    console.log(newData); // Log the newData variable to check its structure and contents

    if (!chart || !chart.data) {
        console.error('Chart or chart data is undefined.');
        return;
    }

    // Extract dates, income, and expense values from the newData object
    const dates = Object.keys(newData);
    const incomes = dates.map(date => newData[date].income);
    const expenses = dates.map(date => newData[date].expense);

    // Update the chart with the new data
    chart.data.labels = dates;
    chart.data.datasets[0].data = incomes;
    chart.data.datasets[1].data = expenses;
    chart.update(); // Update the chart
}


// Get the canvas element
var ctx = document.getElementById("chartjsIncomeVsExpense");

// Check if the canvas element exists
if (ctx) {
    // Initialize the chart
    var myChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: [],
            datasets: [{
                label: 'Income',
                data: [],
                backgroundColor: 'rgba(47, 44, 216,1)',
            },
            {
                label: 'Expense',
                data: [],
                backgroundColor: 'rgba(47, 44, 216,0.2)',
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            legend: {
                display: true,
                labels: {
                    fontColor: 'black', // You can customize the font color
                    fontSize: 12
                }
            },
            scales: {
                xAxes: [{
                    gridLines: {
                        drawBorder: false,
                        display: false
                    },
                    ticks: {
                        display: true, // hide main x-axis line
                        beginAtZero: true
                    },
                    barPercentage: 1,
                    categoryPercentage: 0.5
                }],
                yAxes: [{
                    gridLines: {
                        drawBorder: false, // hide main y-axis line
                        display: false
                    },
                    ticks: {
                        display: true,
                        beginAtZero: true
                    },
                }]
            },
            tooltips: {
                enabled: true
            }
        }
    });
} else {
    console.error('Canvas element with ID "chartjsIncomeVsExpense" not found.');
}



// Define a function to fetch data and update the chart
function updateChart() {
    fetch('/reports/income-vs-expense-data')
    .then(response => response.json())
    .then(newData => {
        updateChartWithNewData(myChart, newData);
    })
    .catch(error => console.error('Error fetching data:', error));
}
