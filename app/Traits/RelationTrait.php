<?php

namespace App\Traits;

use Illuminate\Support\Str;

trait RelationTrait
{
    public function toArray()
    {
        $array = parent::toArray();

        foreach ($this->getRelations() as $relation => $model) {
            if ($model instanceof \Illuminate\Database\Eloquent\Model) {
                
                // Detect morphTo
                if ($relation === 'model' && isset($this->model_type)) {
                    $morphBase = class_basename($this->model_type);
                    $relationName = Str::plural(Str::snake($morphBase));
                } else {
                    $relationName = Str::plural($relation);
                }

                // Merge attributes
                foreach ($model->getAttributes() as $key => $value) {
                    if (is_string($value) && is_array(json_decode($value, true))) {
                        $array[$relationName . "_{$key}"] = json_decode($value, true);
                    } else {
                        $array[$relationName . "_{$key}"] = $value;
                    }
                }

                // Handle nested morph
                foreach ($model->getRelations() as $nestedRelation => $nestedModel) {
                    if ($nestedModel instanceof \Illuminate\Database\Eloquent\Model) {
                        $nestedBase = $nestedRelation === 'model' && isset($model->model_type)
                            ? class_basename($model->model_type)
                            : $nestedRelation;
                        
                        $nestedPrefix = $relationName . '_' . Str::plural(Str::snake($nestedBase));
                        
                        foreach ($nestedModel->getAttributes() as $key => $value) {
                            if (is_string($value) && is_array(json_decode($value, true))) {
                                $array[$nestedPrefix . "_{$key}"] = json_decode($value, true);
                            }
                            else 
                            {
                                
                                $array[$nestedPrefix . "_{$key}"] = $value;
                            }
                        }

                        foreach($nestedModel->getRelations() as $key => $item){
                            if (is_string($item) && is_array(json_decode($item, true))) {
                                $array[$nestedPrefix . "_{$key}"] = json_decode($item, true);
                            }
                            else 
                            {
                                $array[$nestedPrefix . "_{$key}"] = $item;
                            }
                        }
                    }
                    else{
                        $array[$relationName . '_' . Str::snake($nestedRelation)] = $nestedModel;
                    }
                }
            }
        }

        return $array;
    }

}
