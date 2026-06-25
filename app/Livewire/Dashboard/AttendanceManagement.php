<?php

namespace App\Livewire\Dashboard;

use App\Models\Employee;
use App\Models\Attendance;
use App\Models\AttendanceSetting;
use Livewire\Component;
use Livewire\Attributes\Computed;
use Carbon\Carbon;

class AttendanceManagement extends Component
{
    public $tab = 'input'; // input, report, setting

    // Input tab
    public $currentDate;
    public $attendances = [];

    // Report tab
    public $reportMonth;
    public $reportYear;
    
    // Setting tab
    public $offDays = [];
    public $availableDays = [
        0 => 'Minggu',
        1 => 'Senin',
        2 => 'Selasa',
        3 => 'Rabu',
        4 => 'Kamis',
        5 => 'Jumat',
        6 => 'Sabtu',
    ];

    public function mount()
    {
        $this->currentDate = date('Y-m-d');
        $this->reportMonth = date('m');
        $this->reportYear = date('Y');
        
        $this->loadSettings();
        $this->loadDayAttendances();
    }

    public function setTab($tab)
    {
        $this->tab = $tab;
        if ($tab === 'input') {
            $this->loadDayAttendances();
        }
    }

    // -- SETTINGS --
    public function loadSettings()
    {
        $setting = AttendanceSetting::first();
        if ($setting && $setting->off_days) {
            $this->offDays = $setting->off_days;
        }
    }

    public function saveSettings()
    {
        $setting = AttendanceSetting::first();
        if (!$setting) {
            AttendanceSetting::create(['off_days' => $this->offDays]);
        } else {
            $setting->update(['off_days' => $this->offDays]);
        }
        $this->dispatch('savedSuccess', message: 'Pengaturan hari libur berhasil disimpan.');
        $this->loadDayAttendances(); // Refresh today's if it changed
    }

    // -- INPUT ATTENDANCE --
    public function updatedCurrentDate()
    {
        $this->loadDayAttendances();
    }

    public function loadDayAttendances()
    {
        $employees = $this->activeEmployees;
        
        $dayOfWeek = Carbon::parse($this->currentDate)->dayOfWeek;
        $isHoliday = in_array((string)$dayOfWeek, $this->offDays) || in_array((int)$dayOfWeek, $this->offDays);

        $defaultStatus = $isHoliday ? 'holiday' : 'present';

        $records = Attendance::whereDate('date', $this->currentDate)->get()->keyBy('employee_id');

        $this->attendances = [];
        foreach ($employees as $emp) {
            if ($records->has($emp->id)) {
                $this->attendances[$emp->id] = $records[$emp->id]->status;
            } else {
                $this->attendances[$emp->id] = $defaultStatus;
            }
        }
    }

    public function saveAttendances()
    {
        foreach ($this->attendances as $employee_id => $status) {
            Attendance::updateOrCreate(
                ['employee_id' => $employee_id, 'date' => $this->currentDate],
                ['status' => $status]
            );
        }
        
        $this->dispatch('savedSuccess', message: 'Absensi tanggal ' . $this->currentDate . ' berhasil disimpan.');
    }

    #[Computed]
    public function activeEmployees()
    {
        return Employee::where('is_active', true)->orderBy('name')->get();
    }

    // -- REPORT --
    public function getReportDataProperty()
    {
        $month = $this->reportMonth;
        $year = $this->reportYear;

        // Fetch employees with a filtered count of absences in ONE query (avoid N+1)
        $employees = Employee::where('is_active', true)
            ->withCount(['attendances as total_absences' => function ($query) use ($month, $year) {
                $query->whereMonth('date', $month)
                    ->whereYear('date', $year)
                    ->where('status', 'absent');
            }])
            ->orderBy('name')
            ->get();

        $data = [];
        foreach ($employees as $emp) {
            $base = $emp->base_salary;
            $absences = $emp->total_absences; // Already calculated via withCount
            $deduction = $absences * $emp->deduction_per_day;
            
            $data[] = [
                'employee' => $emp,
                'base_salary' => $base,
                'absences' => $absences,
                'deduction' => $deduction,
                'total_salary' => $base - $deduction,
            ];
        }
        return $data;
    }

    public function render()
    {
        return view('livewire.dashboard.attendance-management')->layout('layouts.app');
    }
}
