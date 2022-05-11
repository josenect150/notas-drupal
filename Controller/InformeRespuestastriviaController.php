<?php

namespace Drupal\co_aguilatenderos_core\Controller;

use Drupal;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Provides route responses for the Example module.
 */
class InformeRespuestastriviaController extends ControllerBase
{

    public function test()
    {

        $params = [
            'type' => 'questions',
            'status' => 1
        ];
        $nodes = \Drupal::entityTypeManager()->getStorage('node')->loadByProperties($params);

        foreach ($nodes as $key => $value) {
            $node_questions = $value->get('field_questions_txt');
            $selected_questions = $node_questions->referencedEntities();
            foreach ($selected_questions as $key2 => $valor) {
                $value = $node_questions[$key2];
                $question_id = $value->entity->id();
                $paragraph_type = $value->entity->getType();
                $title = $value->entity->get('field_title')->getString();
                $answers = $value->entity->get('field_answers');
                $type = str_replace('qon_', '', $paragraph_type);
                $preguntas[$question_id] = [
                    'title' => $title,
                    'respuesta' => $type,
                ];
                foreach ($answers as $item) {
                    $entity = $item->entity;
                    $answer_id = ($entity->id());
                    $field_correct = 'field_is_correct';
                    $correct = (bool)$entity->get($field_correct)->getString();
                    if ($type == "text") {
                        $field_data = 'field_opt_txt';
                        $val = $entity->get($field_data)->getString();
                    } else if ($type == "img") {
                        $field_data = 'field_opt_img';
                        $val = $entity->get($field_data)->entity->url();
                    }

                    $preguntas[$question_id]['answers'][$answer_id] = [
                        'value' => $val,
                        'correct' => $correct,
                    ];
                }
            }

            $response[$key] = $preguntas;
        }

        $query = \Drupal::database()->select('co_aguila_core_sw_trivia_score', 'bc');
        $query->fields('bc', []);
        $query->join('co_aguilatenderos_code_bees', 'l', 'bc.id_shop=l.id');
        $query->fields('l', ['name_shop', 'code_bees']);
        $query->orderBy('name_shop', 'DESC');
        $data = $query->execute()->fetchAll();

        foreach ($data as $key => $value) {


            $question = json_decode($value->questions);
            // dd($question,$value);
            if ($question) {
                $r = json_decode(json_encode($question), true);
                foreach ($r as $key2 => $value2) {
                    if (isset($response[$value->trivia_id])) {
                        foreach ($response[$value->trivia_id] as $key3 => $value3) {
                            if ($value3['title'] === $key2) {
                                foreach ($value3['answers'] as $key => $value4) {
                                    if ($value4['value'] === $value2) {
                                        $preguntasUser[] = [
                                            'titulo' => $value3['title'],
                                            'respuesta' => $value2,
                                            'correcta' => $value4['correct'],
                                            'trivia' => $value->trivia_id,
                                            'user' => $value->uid,
                                            'code_bees' => $value->code_bees,
                                            'name_shop' => $value->name_shop
                                        ];
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        // dd($preguntasUser);
        return new JsonResponse($preguntasUser, 200);
    }
}
