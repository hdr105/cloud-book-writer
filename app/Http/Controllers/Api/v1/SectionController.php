<?php

namespace App\Http\Controllers\api\v1;

use App\Models\Section;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class SectionController extends Controller
{

    
    /**
     * @OA\Post(
     *     path="/api/sections",
     *     operationId="createSection",
     *     tags={"Sections"},
     *     summary="Create a new section",
     *     description="Creates a new section within a book",
     *     @OA\RequestBody(
     *         required=true,
     *         description="Section data",
     *         @OA\JsonContent(
     *             @OA\Property(property="title", type="string", example="Section Title"),
     *             @OA\Property(property="description", type="string", example="A brief description of the section."),
     *             @OA\Property(property="book_id", type="integer", example=1),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Section created successfully",
     *         @OA\JsonContent(type="object", @OA\Property(property="success", type="boolean", example=true), @OA\Property(property="msg", type="string", example="Section added successfully"), @OA\Property(property="code", type="integer", example=200)),
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request",
     *         @OA\JsonContent(type="object", @OA\Property(property="success", type="boolean", example=false), @OA\Property(property="msg", type="string", example="Validation failed"), @OA\Property(property="code", type="integer", example=422)),
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Permission Denied",
     *         @OA\JsonContent(type="object", @OA\Property(property="success", type="boolean", example=false), @OA\Property(property="msg", type="string", example="Permission Denied"), @OA\Property(property="code", type="integer", example=403)),
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error",
     *         @OA\JsonContent(type="object", @OA\Property(property="success", type="boolean", example=false), @OA\Property(property="msg", type="string", example="Error try again later!"), @OA\Property(property="code", type="integer", example=500)),
     *     ),
     * )
     */

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


    /**
     * @OA\Post(
     *     path="/api/sub-sections",
     *     operationId="createSubSection",
     *     tags={"Subsections"},
     *     summary="Create a new sub-section",
     *     description="Creates a new sub-section within a section",
     *     @OA\RequestBody(
     *         required=true,
     *         description="Sub-section data",
     *         @OA\JsonContent(
     *             @OA\Property(property="title", type="string", example="Sub-Section Title"),
     *             @OA\Property(property="description", type="string", example="A brief description of the sub-section."),
     *             @OA\Property(property="book_id", type="integer", example=1),
     *             @OA\Property(property="parent_id", type="integer", example=2, description="ID of the parent section (optional)"),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Sub-section created successfully",
     *         @OA\JsonContent(type="object", @OA\Property(property="success", type="boolean", example=true), @OA\Property(property="msg", type="string", example="Sub-section added successfully"), @OA\Property(property="code", type="integer", example=200)),
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request",
     *         @OA\JsonContent(type="object", @OA\Property(property="success", type="boolean", example=false), @OA\Property(property="msg", type="string", example="Validation errors"), @OA\Property(property="errors", type="string", example="The given data was invalid."), @OA\Property(property="code", type="integer", example=422)),
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Permission Denied",
     *         @OA\JsonContent(type="object", @OA\Property(property="success", type="boolean", example=false), @OA\Property(property="msg", type="string", example="Permission Denied"), @OA\Property(property="code", type="integer", example=403)),
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Parent Section Not Found",
     *         @OA\JsonContent(type="object", @OA\Property(property="success", type="boolean", example=false), @OA\Property(property="msg", type="string", example="Parent section not found"), @OA\Property(property="code", type="integer", example=404)),
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error",
     *         @OA\JsonContent(type="object", @OA\Property(property="success", type="boolean", example=false), @OA\Property(property="msg", type="string", example="Error try again later!"), @OA\Property(property="code", type="integer", example=500)),
     *     ),
     * )
     */

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


    /**
     * @OA\Put(
     *     path="/api/sections/{id}",
     *     operationId="updateSection",
     *     tags={"Sections"},
     *     summary="Update an existing section",
     *     description="Updates an existing section's title and description",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the section to update",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="Section data",
     *         @OA\JsonContent(
     *             @OA\Property(property="title", type="string", example="Updated Section Title"),
     *             @OA\Property(property="description", type="string", example="Updated description of the section."),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Section updated successfully",
     *         @OA\JsonContent(type="object", @OA\Property(property="success", type="boolean", example=true), @OA\Property(property="msg", type="string", example="Section updated successfully"), @OA\Property(property="code", type="integer", example=200)),
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request",
     *         @OA\JsonContent(type="object", @OA\Property(property="success", type="boolean", example=false), @OA\Property(property="msg", type="string", example="Validation errors"), @OA\Property(property="errors", type="string", example="The given data was invalid."), @OA\Property(property="code", type="integer", example=422)),
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Permission Denied",
     *         @OA\JsonContent(type="object", @OA\Property(property="success", type="boolean", example=false), @OA\Property(property="msg", type="string", example="Permission Denied"), @OA\Property(property="code", type="integer", example=403)),
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Section Not Found",
     *         @OA\JsonContent(type="object", @OA\Property(property="success", type="boolean", example=false), @OA\Property(property="msg", type="string", example="Section not found"), @OA\Property(property="code", type="integer", example=404)),
     *     ),
     * )
     */

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
