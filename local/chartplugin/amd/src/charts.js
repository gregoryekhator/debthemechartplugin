define(['jquery', 'core/chartjs'], function($, Chart) {

    return {
        init: function() {

            const canvas = document.getElementById('studentchart');
            if (!canvas) {
                return;
            }

            const ctx = canvas.getContext('2d');

            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['Red', 'Blue', 'Yellow', 'Green'],
                    datasets: [{
                        label: 'Learning Progress',
                        data: [12, 19, 7, 15],
                        backgroundColor: 'rgba(54, 162, 235, 0.6)'
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        }
    };
});
