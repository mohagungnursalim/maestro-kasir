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
        $branchId = session('active_branch_id');

        foreach ($this->attendances as $employee_id => $status) {
            Attendance::withoutGlobalScope('branch')->updateOrCreate(
                [
                    'employee_id' => $employee_id,
                    'date'        => $this->currentDate,
                    'branch_id'   => $branchId,
                ],
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

        // Fetch employees with a filtered count of absences and presents in ONE query (avoid N+1)
        $employees = Employee::where('is_active', true)
            ->withCount([
                'attendances as total_absences' => function ($query) use ($month, $year) {
                    $query->whereMonth('date', $month)
                        ->whereYear('date', $year)
                        ->where('status', 'absent');
                },
                'attendances as total_presents' => function ($query) use ($month, $year) {
                    $query->whereMonth('date', $month)
                        ->whereYear('date', $year)
                        ->where('status', 'present');
                }
            ])
            ->orderBy('name')
            ->get();

        $data = [];
        foreach ($employees as $emp) {
            $base = $emp->base_salary;
            $absences = $emp->total_absences; // Already calculated via withCount
            $presents = $emp->total_presents;
            $deduction = $absences * $emp->deduction_per_day;
            
            $data[] = [
                'employee' => $emp,
                'base_salary' => $base,
                'presents' => $presents,
                'absences' => $absences,
                'deduction' => $deduction,
                'total_salary' => $base - $deduction,
            ];
        }
        return $data;
    }

    // -- DETAIL ATTENDANCE --
    public $showDetailModal = false;
    public $detailEmployee = null;
    public $detailAttendances = [];
    public $daysInMonth = [];

    public function viewDetails($employeeId)
    {
        $this->detailEmployee = Employee::find($employeeId);
        if (!$this->detailEmployee) return;

        $month = $this->reportMonth;
        $year = $this->reportYear;

        $records = Attendance::withoutGlobalScope('branch')
            ->where('employee_id', $employeeId)
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
            ->get()
            ->keyBy('date');

        $daysInMonth = Carbon::create($year, $month, 1)->daysInMonth;
        $this->daysInMonth = [];
        $this->detailAttendances = [];

        $today = Carbon::today();
        $daysMap = [0 => 'Min', 1 => 'Sen', 2 => 'Sel', 3 => 'Rab', 4 => 'Kam', 5 => 'Jum', 6 => 'Sab'];

        for ($i = 1; $i <= $daysInMonth; $i++) {
            $dateObj = Carbon::create($year, $month, $i);
            $dateStr = $dateObj->format('Y-m-d');
            $this->daysInMonth[] = [
                'date' => $dateStr,
                'day' => $i,
                'dayName' => $daysMap[$dateObj->dayOfWeek],
            ];

            if ($records->has($dateStr)) {
                $this->detailAttendances[$dateStr] = $records[$dateStr]->status;
            } else {
                if ($dateObj->greaterThan($today)) {
                    $this->detailAttendances[$dateStr] = 'future';
                } else {
                    $dayOfWeek = $dateObj->dayOfWeek;
                    $isHoliday = in_array((string)$dayOfWeek, $this->offDays) || in_array((int)$dayOfWeek, $this->offDays);
                    $this->detailAttendances[$dateStr] = $isHoliday ? 'holiday' : 'unrecorded';
                }
            }
        }

        $this->showDetailModal = true;
    }

    public function closeDetailModal()
    {
        $this->showDetailModal = false;
        $this->detailEmployee = null;
    }

    public function render()
    {
        return view('livewire.dashboard.attendance-management')->layout('layouts.app');
    }
}
