<?php

namespace App\Http\Controllers\Api;

use App\Box;
use App\Http\Controllers\Controller;
use App\Http\Requests\Box\StoreRequest;
use App\Point;
use App\Sheet;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BoxController extends Controller
{
    const COMMAND_START = 'START';
    const COMMAND_DOWN = 'DOWN';
    const COMMAND_UP = 'UP';
    const COMMAND_GO_TO = 'GOTO';
    const COMMAND_STOP = 'STOP';

    protected $instructions;

    protected $cutterVerticalPosition = self::COMMAND_UP;

    public function __construct()
    {
        $this->instructions = collect();
    }

    /**
     * Get instructions to cut boxes from sheet
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $validationRules = (new StoreRequest)->rules();

        $validator = Validator::make($request->all(), $validationRules);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => 'Invalid input format. Please use only positive integers',
            ], 422);
        }

        $data = $validator->validated();

        $box = new Box([
            'width' => $data['boxSize']['w'],
            'deep' => $data['boxSize']['d'],
            'height' => $data['boxSize']['h'],
        ]);

        $sheet = new Sheet([
            'width' => $data['sheetSize']['w'],
            'length' => $data['sheetSize']['l'],
        ]);

        if ($this->isSheetHasEnoughSize($sheet, $box)) {

            return response()->json([
                'success' => false,
                'error' => 'Invalid sheet size. Too small for producing at least one box',
            ], 422);
        }

        $this->cutBoxes($sheet, $box);

        return response()->json([
            'success' => true,
            'amount' => $this->getBoxCountByWidth($sheet, $box) * $this->getBoxCountByHeight($sheet, $box),
            'program' => $this->instructions,
        ]);
    }

    /**
     * Is sheet has enough size
     *
     * @param Sheet $sheet
     * @param Box $box
     * @return bool
     */
    protected function isSheetHasEnoughSize(Sheet $sheet, Box $box): bool
    {
        $minWidth = $this->getSheetMinWidth($box);
        $minLength = $this->getSheetMinLength($box);

        return ($sheet->width < $minWidth || $sheet->length < $minLength)
            && ($sheet->length < $minWidth || $sheet->width < $minLength);
    }

    /**
     * Get sheet min width
     *
     * @param Box $box
     * @return float|int
     */
    protected function getSheetMinWidth(Box $box)
    {
        return 2 * $box->height + 2 * $box->width;
    }

    /**
     * Get sheet min length
     *
     * @param Box $box
     * @return float|int|mixed
     */
    protected function getSheetMinLength(Box $box)
    {
        return 2 * $box->height + $box->deep;
    }

    protected function cutBoxes(Sheet $sheet, Box $box)
    {
        $startPoint = new Point();

        $startPoint->x = 0;
        $startPoint->y = 0;

        $currentPoint = $startPoint;

        $this->createStep(self::COMMAND_START);

        $minWidth = $this->getSheetMinWidth($box);
        $minLength = $this->getSheetMinLength($box);

        $boxCountByWidth = $this->getBoxCountByWidth($sheet, $box);
        $boxCountByLength = $this->getBoxCountByHeight($sheet, $box);

        for ($i = 0; $i < $boxCountByWidth; $i++) {
            $startPoint->x = $minWidth * $i;
            for ($j = 0; $j < $boxCountByLength; $j++) {
                $startPoint->y = $minLength * $j;
                $this->cutBox($currentPoint, $sheet, $box);
            }
        }

        $this->createStep(self::COMMAND_STOP);
    }

    /**
     * getBoxCountByWidth
     *
     * @param Sheet $sheet
     * @param Box $box
     * @return int
     */
    protected function getBoxCountByWidth(Sheet $sheet, Box $box): int
    {
        $minWidth = $this->getSheetMinWidth($box);

        return floor($sheet->width / $minWidth);
    }

    /**
     * getBoxCountByHeight
     *
     * @param Sheet $sheet
     * @param Box $box
     * @return int
     */
    protected function getBoxCountByHeight(Sheet $sheet, Box $box): int
    {
        $minLength = $this->getSheetMinLength($box);

        return floor($sheet->length / $minLength);
    }

    /**
     * Get instructions to cut box
     *
     * @param Point $currentPoint
     * @param Sheet $sheet
     * @param Box $box
     */
    protected function cutBox(Point $currentPoint, Sheet $sheet, Box $box)
    {
        $currentPoint->x += $box->height; // go right
        $this->moveTo($currentPoint);
        $currentPoint->y += $box->height; // go up
        $this->cutTo($currentPoint);
        $currentPoint->x -= $box->height; // go left
        $this->cutTo($currentPoint);

        $currentPoint->y += $box->deep; // go up
        $this->moveTo($currentPoint);

        $currentPoint->x += $box->height; // go right
        $this->cutTo($currentPoint);

        $currentPoint->y += $box->height; // go up
        $this->cutTo($currentPoint);

        $currentPoint->x += $box->width; // go right
        $this->cutTo($currentPoint);

        $currentPoint->y -= $box->height; // go down
        $this->cutTo($currentPoint);

        $currentPoint->x += $box->height; // go right
        $this->cutTo($currentPoint);

        $currentPoint->x += $box->width; // go right
        $this->cutTo($currentPoint);

        $currentPoint->y -= $box->deep; // go down
        $this->cutTo($currentPoint);

        $currentPoint->x -= $box->width; // go left
        $this->cutTo($currentPoint);

        $currentPoint->x -= $box->height; // go left
        $this->cutTo($currentPoint);

        $currentPoint->y -= $box->height; // go left
        $this->cutTo($currentPoint);

        $currentPoint->x -= $box->height; // go left
        $this->moveTo($currentPoint);
    }

    /**
     * Move cutter to coordinate
     *
     * @param Point $point
     */
    protected function moveTo(Point $point)
    {
        if ($this->cutterVerticalPosition !== self::COMMAND_UP) {
            $this->createStep(self::COMMAND_UP);
            $this->cutterVerticalPosition = self::COMMAND_UP;
        }
        $this->createStep(self::COMMAND_GO_TO, $point->x, $point->y);
    }

    /**
     * Cut to coordinate
     *
     * @param Point $point
     */
    protected function cutTo(Point $point)
    {
        if ($this->cutterVerticalPosition !== self::COMMAND_DOWN) {
            $this->createStep(self::COMMAND_DOWN);
            $this->cutterVerticalPosition = self::COMMAND_DOWN;
        }
        $this->createStep(self::COMMAND_GO_TO, $point->x, $point->y);
    }

    /**
     * Create step
     *
     * @param $command
     * @param null $coordinateX
     * @param null $coordinateY
     */
    protected function createStep($command, $coordinateX = null, $coordinateY = null)
    {
        $stepData = [
            'command' => $command,
        ];

        if ($command === self::COMMAND_GO_TO) {
            $stepData['x'] = $coordinateX;
            $stepData['y'] = $coordinateY;
        }

        $this->instructions->push($stepData);
    }
}
