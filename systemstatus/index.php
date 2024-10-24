<?php
//header(“Access-Control-Allow-Origin: http://192.168.32.133”);
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS, post, get');
header("Access-Control-Max-Age", "3600");
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token');
//header("Access-Control-Allow-Credentials", "true");


//Read more here: https://locall.host/php-cors-policy-no-access-control-allow-origin/
?>
<script src="https://code.highcharts.com/highcharts.js"></script>
<div id="container" style="height: 300px"></div>
<script>
document.addEventListener('DOMContentLoaded', function () {
    Highcharts.chart('container', {
        chart: {
            type: 'bar'
        },
        title: {
            text: 'Fruit Consumption'
        },
        xAxis: {
            categories: ['Apples', 'Bananas', 'Oranges']
        },
        yAxis: {
            title: {
                text: 'Fruit eaten'
            }
        },
        series: [{
            name: 'Jane',
            data: [1, 0, 4]
        }, {
            name: 'John',
            data: [5, 7, 3]
        }],
    });
});
</script>
