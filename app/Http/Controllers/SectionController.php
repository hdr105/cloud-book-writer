<?php

namespace App\Http\Controllers;

use App\Models\Section;
use Illuminate\Http\Request;

class SectionController extends Controller
{
    public function store(Request $request) {
        if ($this->authorize('create-sections')) {
            $section = Section::create([
                'title'=> $request->title,
                'description'=> $request->description,
                'book_id'=> $request->book_id,
                'parent_id'=> null,
            ]);

            if ($section) {
                return response()->json([
                    "success" => true,
                    "msg" => "Section added successfully",
                    "code" => 200
                ]);
            } else {
                return response()->json([
                    "success" => false,
                    "msg" => "Error try again later!",
                    "code" => 500
                ]);
            }
        } else {
            return response()->json([
                "success" => false,
                "msg" => "Permission Denied",
                "code" => 403
            ]);
        }

    }

    public function storeSubSection(Request $request) {
        if ($this->authorize('create-sections')) {
            $section = Section::create([
                'title'=> $request->title,
                'description'=> $request->description,
                'book_id'=> $request->book_id,
                'parent_id'=> $request->parent_id,
            ]);

            if ($section) {
                return response()->json([
                    "success" => true,
                    "msg" => "Section added successfully",
                    "code" => 200
                ]);
            } else {
                return response()->json([
                    "success" => false,
                    "msg" => "Error try again later!",
                    "code" => 500
                ]);
            }
        } else {
            return response()->json([
                "success" => false,
                "msg" => "Permission Denied",
                "code" => 403
            ]);
        }

    }

    public function getSection(Section $section)
    {
        if($this->authorize('edit-sections')) {
            return response()->json($section);
        } else {
            return response()->json([
                "success" => false,
                "msg" => "Permission Denied",
                "code" => 403
            ]);
        }
    }

    public function update(Request $request)
    {
        if($this->authorize('update-sections')) {

            // Validate the request data
            $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
            ]);
    
            // Update the section with the new data
            $section = Section::find($request->section_id);
    
            if ($section) {
                $section->update([
                    'title' => $request->title,
                    'description' => $request->description,
                ]);
            }
    
            return response()->json([
                "success" => true,
                "msg" => "Added Successfully",
                "code" => 200
            ]);
        } else {

        }
    }
}
