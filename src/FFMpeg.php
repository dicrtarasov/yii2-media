<?php
namespace dicr\media;

use dicr\exec\ExecInterface;
use dicr\exec\LocalExec;
use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\helpers\Json;

/**
 * Интерфейс команды FFMpeg
 *
 * @author Igor (Dicr) Tarasov <develop@dicr.org>
 * @version 180626
 */
class FFMpeg extends Component
{
    /** @var \dicr\exec\ExecInterface исполнитель системных комманд */
    public $exec = [
        'class' => LocalExec::class
    ];

    /** @var string путь команды ffmpeg */
    public $ffmpeg = '/usr/bin/ffmpeg';

    /** @var string путь команды ffprobe */
    public $ffprobe = '/usr/bin/ffprobe';

    /**
     * {@inheritdoc}
     * @see \yii\base\BaseObject::init()
     */
    public function init()
    {
        if (is_array($this->exec)) {
            $this->exec = \Yii::createObject($this->exec);
        }

        if (! ($this->exec instanceof ExecInterface)) {
            throw new InvalidConfigException('exec');
        }

        if (empty($this->ffmpeg)) {
            throw new InvalidConfigException('ffmpeg');
        }
    }

    /**
     * ПОлучить информацию о медиа
     *
     * @param string $path путь или url
     * @throws \InvalidArgumentException
     * @return array|false
     */
    public function getMediaInfo(string $path)
    {
        if ($path == '') {
            throw new \InvalidArgumentException('empty path');
        }

        $json = $this->exec->run(
            $this->ffprobe,
            ['-v','error','-show_format','-show_streams','-of','json','-i',$path],
            ['escape' => true]
        );

        $json = Json::decode($json, true);
        if ($json === null) {
            $json = false;
        }

        return $json;
    }

    /**
     * Запускает ffmpeg
     *
     * @param array $args аргументы по одному в элементах.
     * @param array $opts опции, передаваемые Exec
     * @throws \InvalidArgumentException
     * @return string|false
     * @see \dicr\exec\Exec#run
     */
    public function run(array $args, array $opts = [])
    {
        if (empty($args)) {
            throw new \InvalidArgumentException('empty args');
        }

        // дополнительные аргументы
        if (! in_array('-nostdin', $args)) {
            array_unshift($args, '-nostdin');
        }

        if (! in_array('-y', $args)) {
            array_unshift($args, '-y');
        }

        if (! in_array('-ignore_unknown', $args)) {
            array_unshift($args, '-ignore_unknown');
        }

        if (! in_array('-v', $args)) {
            array_unshift($args, 'error');
            array_unshift($args, '-v');
        }

        // запускаем
        return $this->exec->run($this->ffmpeg, $args, $opts);
    }

    /**
     * Конвертирует медиа
     *
     * @param string $src исходник
     * @param string $dst назначение
     * @param array $args дополнительные аргументы
     * @return string|false вывод команды
     */
    public function convert(string $src, string $dst, array $args = [])
    {
        if (empty($src)) {
            throw new \InvalidArgumentException('src');
        }

        if (empty($dst)) {
            throw new \InvalidArgumentException('dst');
        }

        // входящий файл добавляем перед опциями исходящего
        array_unshift($args, $src);
        array_unshift($args, '-i');

        // в самом конце добавляем исходящий файл
        $args[] = $dst;

        return $this->run($args, ['escape' => true]);
    }

    /**
     * Создает постер видео.
     *
     * @param string $src источник видео
     * @param string $dst путь картинки
     * @param array $args дополнительные аргументы
     * @return string вывод команды
     */
    public function poster(string $src, string $dst, array $args = [])
    {
        if (! in_array('-ss', $args)) {
            $args[] = '-ss';
            $args[] = '00:00:03';
        }

        if (! in_array('-an', $args)) {
            $args[] = '-an';
        }

        if (! in_array('-sn', $args)) {
            $args[] = '-sn';
        }

        if (! in_array('-dn', $args)) {
            $args[] = '-dn';
        }

        if (! in_array('-r', $args)) {
            $args[] = '-r';
            $args[] = 1;
        }

        if (! in_array('-frames:v', $args)) {
            $args[] = '-frames:v';
            $args[] = 1;
        }

        if (! in_array('-f', $args)) {
            $args[] = '-f';
            $args[] = 'image2';
        }

        // выполняем команду
        return $this->convert($src, $dst, $args);
    }
}