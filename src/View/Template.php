<?php declare(strict_types=1);

namespace PMRAtk\View;

use atk4\data\Model;
use DateTimeInterFace;
use traitsforatkdata\DateTimeHelpersTrait;
use ReflectionClass;

class Template extends \atk4\ui\Template
{

    use DateTimeHelpersTrait;

    /*
     *
     */
    public function setSTDValues()
    {
        $this->trySet($this->app->getAllSTDSettings());
    }


    /*
     *
     */
    public function setGermanList(string $tag, array $a)
    {
        $string = '';
        $counter = 0;
        foreach ($a as $item) {
            $counter++;
            if (empty($item)) {
                continue;
            }
            if ($counter === 1) {
                $string .= $item;
            } elseif ($counter === count($a)) {
                $string .= ' und ' . $item;
            } else {
                $string .= ', ' . $item;
            }
        }
        $this->set($tag, $string);
    }


    /*
     * Tries to set each passed tag with its value from passed model
     */
    public function setTagsFromModel(Model $model, array $tags = [], string $prefix = null)
    {
        if (!$tags) {
            $tags = array_keys($model->getFields());
        }
        if ($prefix === null) {
            $prefix = strtolower((new ReflectionClass($model))->getShortName()) . '_';
        }

        foreach ($tags as $tag) {
            if (
                !$model->hasField($tag)
                || !$this->hasTag($prefix . $tag)
            ) {
                continue;
            }

            //try converting non-scalar values
            if (!is_scalar($model->get($tag))) {
                if ($model->get($tag) instanceof DateTimeInterFace) {
                    $this->set(
                        $prefix . $tag,
                        $this->castDateTimeToGermanString($model->getField($tag), true)
                    );
                } else {
                    $this->set($prefix . $tag, $model->getField($tag)->toString());
                }
            } else {
                switch ($model->getField($tag)->type) {
                    case 'text':
                        $this->setHTML(
                            $prefix . $tag,
                            nl2br(
                                htmlspecialchars(
                                    $this->app->ui_persistence->typecastSaveField(
                                        $model->getField($tag),
                                        $model->get($tag)
                                    )
                                )
                            )
                        );
                        break;
                    default:
                        $this->set(
                            $prefix . $tag,
                            $this->app->ui_persistence->typecastSaveField(
                                $model->getField($tag),
                                $model->get($tag)
                            )
                        );
                        break;
                }
            }
        }
    }


    /**
     *
     */
    public function setWithLineBreaks(string $tag, string $value)
    {
        $this->setHTML($tag, nl2br(htmlspecialchars($value)));
    }


    /**
     *
     */
    public function replaceHTML(string $region, string $content)
    {
        $this->del($region);
        $this->appendHTML($region, $content);
    }
}
