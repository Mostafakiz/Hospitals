<?php
namespace App\Repository\Doctors;

use App\Interfaces\Doctors\DoctorRepositoryInterface;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Image;
use App\Models\Section;
use App\Traits\UploadTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DoctorRepository implements DoctorRepositoryInterface
{
    use UploadTrait;
    public function index()
    {
        $doctors = Doctor::with('doctorAppointments')->get();
        return view('Dashboard.Doctors.index', compact('doctors'));
    }
    public function create()
    {
        $sections = Section::all();
        $appointments = Appointment::all();
        return view('Dashboard.Doctors.add', compact('sections', 'appointments'));
    }
    public function store($request)
    {
        DB::beginTransaction();
        try {
            $doctor = new Doctor();
            $doctor->email = $request->email;
            $doctor->password = Hash::make($request->password);
            $doctor->section_id = $request->section_id;
            $doctor->phone = $request->phone;
            $doctor->status = 1;
            $doctor->save();
            $doctor->name = $request->name;
            $doctor->save();
            $this->verifyAndStoreImage($request, 'photo', 'doctors', 'upload_image', $doctor->id, 'App\Models\Doctor');
            DB::commit();
            session()->flash('add');
            return redirect()->route('Doctors.create');
        } catch (\Exception $ex) {
            DB::rollBack();
            return redirect()->back()->withErrors(['error' => $ex->getMessage()]);
        }
    }
    public function destroy($request)
    {
        if ($request->page_id == 1) {

            if ($request->filename) {

                $this->Delete_attachment('upload_image', 'doctors/' . $request->filename, $request->id, $request->filename);
            }
            Doctor::destroy($request->id);
            session()->flash('delete');
            return redirect()->route('Doctors.index');
        } else {
            $delete_select_id = explode(',', $request->delete_select_id);
            foreach ($delete_select_id as $ids_doctors) {
                $doctor = Doctor::findOrfail($ids_doctors);
                if ($doctor->image) {
                    $this->Delete_attachment('upload_image', 'doctors/' . $doctor->image->filename, $ids_doctors, $doctor->image->filename);
                }
            }
            Doctor::destroy($delete_select_id);
            session()->flash('delete');
            return redirect()->route('Doctors.index');
        }
    }
    public function edit($id)
    {
        $sections = Section::all();
        $appointments = Appointment::all();
        $doctor = Doctor::findorfail($id);
        return view('Dashboard.Doctors.edit', compact('sections', 'appointments', 'doctor'));
    }
    public function update($request)
    {
        DB::beginTransaction();
        try {
            $doctor = Doctor::findOrfail($request->id);
            $doctor->email = $request->email;
            $doctor->section_id = $request->section_id;
            $doctor->phone = $request->phone;
            $doctor->save();
            // store trans
            $doctor->name = $request->name;
            $doctor->save();
            // update pivote table
            $doctor->doctorAppointments()->sync($request->appointments);
            // update photo
            if ($request->has('photo')) {
                // Delete old photo
                if ($doctor->image) {
                    $old_img = $doctor->image->filename;
                    $this->Delete_attachment('upload_image', 'doctors/' . $old_img, $request->id);
                }
                //Upload img
                $this->verifyAndStoreImage($request, 'photo', 'doctors', 'upload_image', $request->id, 'App\Models\Doctor');
            }
            DB::commit();
            session()->flash('edit');
            return redirect()->back();

        } catch (\Exception $exception) {
            DB::rollBack();
            return redirect()->back()->withErrors(['error' => $exception->getMessage()]);
        }
    }
}