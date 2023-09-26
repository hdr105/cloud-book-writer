<?php

namespace App\Http\Controllers\api\v1;

use App\Models\Section;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class SectionController extends Controller
{
    public function section_store(Request $request) {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string',
            'description' => 'required|string',
            'book_id' => 'required|exists:books,id', 
        ]);
    
        // Check if validation fails
        if ($validator->fails()) {
            return response()->json([
                "success" => false,
                "msg" => $validator->errors()->first(),
                "code" => 422
            ]);
        }
    
        // Check if the user is authorized to create sections
        if ($this->authorize('create-sections')) {
            $section = Section::create([
                'title' => $request->title,
                'description' => $request->description,
                'book_id' => $request->book_id,
                'parent_id' => null,
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

    public function sub_section_store(Request $request) {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string',
            'description' => 'required|string',
            'book_id' => 'required|exists:books,id',
            'parent_id' => 'nullable|exists:sections,id',
        ]);

        // Check if validation fails
        if ($validator->fails()) {
            return response()->json([
                "success" => false,
                "msg" => "Validation errors",
                "errors" => $validator->errors()->first(),
                "code" => 422
            ]);
        }

        // Check if the user is authorized to create sections
        if ($this->authorize('create-sections')) {
            // Check if the parent section exists if provided
            if ($request->has('parent_id')) {
                $parentSection = Section::find($request->parent_id);
                if (!$parentSection) {
                    return response()->json([
                        "success" => false,
                        "msg" => "Parent section not found",
                        "code" => 404
                    ]);
                }
            }

            $section = Section::create([
                'title' => $request->title,
                'description' => $request->description,
                'book_id' => $request->book_id,
                'parent_id' => $request->parent_id,
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

    public function section_update(Request $request) {
        // Validation rules for the request parameters
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:sections,id',
            'title' => 'required|string',
            'description' => 'required|string',
        ]);

        // Check if validation fails
        if ($validator->fails()) {
            return response()->json([
                "success" => false,
                "msg" => $validator->errors()->first(),
                "code" => 422
            ]);
        }

        // Find the section by ID
        $section = Section::find($request->id);

        // Check if the section exists
        if (!$section) {
            return response()->json([
                "success" => false,
                "msg" => "Section not found",
                "code" => 404
            ]);
        }

        // Check if the user is authorized to update sections
        if ($this->authorize('update-sections')) {
            // Update the section's title and description
            $section->update([
                'title' => $request->title,
                'description' => $request->description,
            ]);

            return response()->json([
                "success" => true,
                "msg" => "Section updated successfully",
                "code" => 200
            ]);
        } else {
            return response()->json([
                "success" => false,
                "msg" => "Permission Denied",
                "code" => 403
            ]);
        }

    }
}
