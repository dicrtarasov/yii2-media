<?php
namespace dicr\tests;


use dicr\media\FFMpeg;

/**
 * Test CSVFile
 *
 * @author Igor (Dicr) Tarasov <develop@dicr.org>
 * @version 2018
 */
class FFMpegTest extends TestCase {

	/** @var array тстовые данные */
	const TEST_FILE = __DIR__ . '/test.mp4';

	/** @var FFMpeg */
	protected $ffmpeg;

	/**
	 * {@inheritDoc}
	 * @see \dicr\tests\TestCase::setUp()
	 */
	public function setUp() {
	    $this->config['components']['ffmpeg'] = [
	        'class' => FFMpeg::class
	    ];

	    parent::setUp();

	    $this->ffmpeg = \Yii::$app->ffmpeg;
	}

	/**
	 * Тест
	 */
	public function testMediaInfo() {
	    $json = $this->ffmpeg->getMediaInfo(self::TEST_FILE);
	    static::assertEquals('aac', $json['streams'][0]['codec_name']);
	    static::assertEquals('22050', $json['streams'][0]['sample_rate']);
	    static::assertEquals('h264', $json['streams'][1]['codec_name']);
	    static::assertEquals('640', $json['streams'][1]['width']);
	    static::assertEquals('360', $json['streams'][1]['height']);
	    static::assertEquals('60.095000', $json['format']['duration']);
	}
}
