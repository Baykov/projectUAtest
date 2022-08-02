<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace app\commands;

use app\services\Importer;
use Yii;
use yii\console\Controller;
use yii\console\ExitCode;

/**
 * This command echoes the first argument that you have entered.
 *
 * This command is provided as an example for you to learn how to create console commands.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class DataController extends Controller
{
    /**
     * This command echoes what you have entered as the message.
     * @param string $message the message to be echoed.
     * @return int Exit code
     */
    public function actionImport(): int
	{
    	$dataType = 'tenders';
    	$importer = new Importer($dataType);
		Yii::info('start import ' . $dataType, 'console');
		$result = $importer->import();
		if (!$result) {
			return ExitCode::UNSPECIFIED_ERROR;
		}
        return ExitCode::OK;
    }
}
