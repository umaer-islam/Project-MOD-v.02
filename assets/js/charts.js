// Chart.js Theme — Mamun's Ortho Dental (Navy & Gold)
Chart.defaults.color = '#7c7c7c';
Chart.defaults.font.family = "'Outfit', sans-serif";
Chart.defaults.font.size = 12;

document.addEventListener('DOMContentLoaded', () => {

    setTimeout(() => {
        initPatientChart();
    }, 700);

    function initPatientChart() {
        const skeleton = document.getElementById('patientChartSkeleton');
        if (skeleton) skeleton.classList.add('hidden');

        const ctx = document.getElementById('patientChart');
        if (!ctx) return;

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                datasets: [{
                    label: 'Patients',
                    data: [12, 19, 15, 22, 14, 28, 8],
                    borderColor: '#004591',
                    backgroundColor: (context) => {
                        const chart = context.chart;
                        const { ctx: c, chartArea } = chart;
                        if (!chartArea) return 'rgba(0,69,145,0.05)';
                        const gradient = c.createLinearGradient(0, chartArea.top, 0, chartArea.bottom);
                        gradient.addColorStop(0, 'rgba(0,69,145,0.12)');
                        gradient.addColorStop(1, 'rgba(0,69,145,0.00)');
                        return gradient;
                    },
                    borderWidth: 2.5,
                    tension: 0.45,
                    fill: true,
                    pointBackgroundColor: '#fff',
                    pointBorderColor: '#004591',
                    pointBorderWidth: 2.5,
                    pointRadius: 5,
                    pointHoverRadius: 7,
                    pointHoverBackgroundColor: '#ea741b',
                    pointHoverBorderColor: '#ea741b',
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#fff',
                        titleColor: '#004591',
                        bodyColor: '#7c7c7c',
                        borderColor: '#f1f5f9',
                        borderWidth: 1,
                        padding: 12,
                        displayColors: false,
                        titleFont: { weight: '700', size: 13 },
                        callbacks: {
                            label: (context) => `  ${context.parsed.y} patients`,
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { color: 'rgba(0,69,145,0.04)', drawBorder: false },
                        ticks: { color: '#7c7c7c', font: { size: 11, weight: '600' } },
                        border: { display: false }
                    },
                    y: {
                        grid: { color: 'rgba(0,69,145,0.04)', drawBorder: false },
                        ticks: { color: '#7c7c7c', font: { size: 11, weight: '600' } },
                        border: { display: false },
                        beginAtZero: true
                    }
                }
            }
        });
    }
});
