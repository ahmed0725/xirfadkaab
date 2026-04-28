<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-slate-800">School Dashboard</h2>
    </x-slot>
    <div class="space-y-4">
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-5">
            <div class="metric-card"><p class="text-sm text-slate-500">Total Students</p><p class="mt-1 text-3xl font-bold text-blue-700">{{ $stats['students'] }}</p></div>
            <div class="metric-card"><p class="text-sm text-slate-500">Total Classes</p><p class="mt-1 text-3xl font-bold text-indigo-700">{{ $stats['classes'] }}</p></div>
            <div class="metric-card"><p class="text-sm text-slate-500">Fees Collected</p><p class="mt-1 text-3xl font-bold text-emerald-600">${{ number_format($stats['fees_collected'], 2) }}</p></div>
            <div class="metric-card"><p class="text-sm text-slate-500">Pending Fees</p><p class="mt-1 text-3xl font-bold text-rose-600">${{ number_format($stats['pending_fees'], 2) }}</p></div>
            <div class="metric-card"><p class="text-sm text-slate-500">Today Attendance</p><p class="mt-1 text-sm text-slate-600">P: {{ $stats['present_today'] }} | A: {{ $stats['absent_today'] }} | L: {{ $stats['late_today'] }}</p></div>
        </div>

        <div class="grid grid-cols-1 gap-4 xl:grid-cols-2">
            <div class="card">
                <h3 class="mb-3 text-sm font-semibold text-slate-700">Fee Collection Trend (Last 7 dates)</h3>
                <canvas id="feesChart"></canvas>
            </div>
            <div class="card">
                <h3 class="mb-3 text-sm font-semibold text-slate-700">Attendance Distribution</h3>
                <canvas id="attendanceChart"></canvas>
            </div>
        </div>

        <div class="card text-slate-700">
            <p class="font-semibold">Welcome to Xirfad Kaab Schools Management System</p>
            <p class="mt-2 text-sm text-slate-500">Use the left sidebar to manage students, classes, subjects, attendance, fees, reports, and settings.</p>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const feesCtx = document.getElementById('feesChart');
            if (feesCtx && window.Chart) {
                new window.Chart(feesCtx, {
                    type: 'line',
                    data: {
                        labels: @json($charts['fee_labels']),
                        datasets: [{
                            label: 'Fees Collected',
                            data: @json($charts['fee_values']),
                            borderColor: 'rgb(37, 99, 235)',
                            backgroundColor: 'rgba(37, 99, 235, 0.15)',
                            tension: 0.35,
                            fill: true
                        }]
                    },
                    options: { responsive: true, plugins: { legend: { display: false } } }
                });
            }

            const attendanceCtx = document.getElementById('attendanceChart');
            if (attendanceCtx && window.Chart) {
                new window.Chart(attendanceCtx, {
                    type: 'doughnut',
                    data: {
                        labels: @json($charts['attendance_labels']),
                        datasets: [{
                            data: @json($charts['attendance_values']),
                            backgroundColor: ['#059669', '#e11d48', '#f59e0b']
                        }]
                    },
                    options: { responsive: true }
                });
            }
        });
    </script>
</x-app-layout>
