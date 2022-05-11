<?php

/**
 * @file
 * Contains \Drupal\africa_corona_agegate\Form\AgegateAdminForm
 */

 //configuracion semanas para juego # ranking

namespace Drupal\pe_mikes_core_shopkeeper\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Form\FormStateInterface;
use Drupal\file\Entity\File;

class DatesForm extends ConfigFormBase
{

  /** @var string Config settings */
  const SETTINGS = 'dates.config.shopkeeper';

  /**
   * {@inheritdoc}
   */
  public function getFormId()
  {
    return 'dates_admin_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames()
  {
    return [
      static::SETTINGS,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state)
  {

    $config = $this->config('dates.config.shopkeeper');
    $form['weeks'] = [
      '#type' => 'fieldset',
      '#title' => 'Configuracion de semanas Bodegueros',
      '#attributes' => ['style' => 'max-width:465px']
    ];
    $form['weeks']['date_start_week'] = [
      '#type' => 'datetime',
      '#title' => t('Configuracion semanas inicio'),
      '#attributes' => [],
      '#default_value' => isset($config->get('config_week_shopkeeper')['date_init']) ? new DrupalDateTime($config->get('config_week_shopkeeper')['date_init']) : '',
      '#description' => t('Seleccione la fecha en la que iniciara'),
      '#required' => TRUE,
    ];
    $form['weeks']['n_week'] = [
      '#type' => 'number',
      '#title' => t('Numero de semanas'),
      '#attributes' => [],
      '#default_value' => isset($config->get('config_week_shopkeeper')['n_weeks']) ? $config->get('config_week_shopkeeper')['n_weeks'] : '',
      '#description' => t('Numero de semanas'),
      '#required' => TRUE,
    ];
    if (isset($config->get('config_week_shopkeeper')['weeks'])) {
      foreach ($config->get('config_week_shopkeeper')['weeks'] as $key => $value) {

        $form['weeks']['week' . $key] = [
          '#type' => 'textfield',
          '#title' => $value['name'],
          '#default_value' => $value['date_init'] . ' / ' . $value['date_end'],
          '#disabled' => TRUE,
        ];
      }
    }

    $form['config_points'] = [
      '#type' => 'fieldset',
      '#title' => 'Configuracion Usuarios y Puntos',
      '#attributes' => ['style' => 'max-width:465px']
    ];
    $form['config_points']['n_user'] = [
      '#type' => 'number',
      '#title' => t('Numeros de usuarios en la semana '),
      '#attributes' => [],
      '#default_value' => isset($config->get('config_points_user_week_shopkeeper')['n_user']) ? $config->get('config_points_user_week_shopkeeper')['n_user'] : '',
      '#description' => t('Numeros de usuarios habilitados por semana'),
      '#required' => TRUE,
    ];
    $form['config_points']['p_u_record'] = [
      '#type' => 'number',
      '#title' => t('Puntos registro'),
      '#attributes' => [],
      '#default_value' => isset($config->get('config_points_user_week_shopkeeper')['p_u_record']) ? $config->get('config_points_user_week_shopkeeper')['p_u_record'] : '',
      '#description' => t('puntos al registrarse en la plataforma'),
      '#required' => TRUE,
    ];
    $form['config_points']['n_u_ranking'] = [
      '#type' => 'number',
      '#title' => t('Numero de ganadores'),
      '#attributes' => [],
      '#default_value' => isset($config->get('config_points_user_week_shopkeeper')['n_u_ranking']) ? $config->get('config_points_user_week_shopkeeper')['n_u_ranking'] : '',
      '#description' => t('Numeros de ganadores en ranking'),
      '#required' => TRUE,
    ];
    $form['config_points']['p_u_ranking'] = [
      '#type' => 'number',
      '#title' => t('Puntos para Ranking'),
      '#attributes' => [],
      '#default_value' => isset($config->get('config_points_user_week_shopkeeper')['p_u_ranking']) ? $config->get('config_points_user_week_shopkeeper')['p_u_ranking'] : '',
      '#description' => t('puntos para los usuarios que alcanzaron el ranking'),
      '#required' => TRUE,
    ];

    return parent::buildForm($form, $form_state);
  }

  public function submitForm(array &$form, FormStateInterface $form_state)
  {
    if ($form_state->getValue('n_week')) {

      date_default_timezone_set('America/Bogota');
      $dateStart = $form_state->getValue('date_start_week')->format('Y-m-d');
      $dates = [];
      for ($i = 1; $i <= $form_state->getValue('n_week'); $i++) {
        $dateweek = new \DateTime($dateStart);
        $dateweek = $dateweek->modify('+1 week');
        $dateweek->modify('-1 day');
        $dateEnd = $dateweek->format('Y-m-d');

        $dates[] = [
          'id' => $i,
          'name' =>  'Semana ' . $i,
          'date_init' =>  $dateStart,
          'date_end' => $dateEnd
        ];

        $dateweek->modify('+1 day');
        $dateStart = $dateweek->format('Y-m-d');
      }
      $data = [
        'weeks' => $dates,
        'date_init' => $form_state->getValue('date_start_week')->format('Y-m-d H:i:s'),
        'n_weeks' => $form_state->getValue('n_week')
      ];
      $this->configFactory->getEditable(static::SETTINGS)
        ->set('config_week_shopkeeper', $data)
        ->save();
    }


    if ($form_state->getValue('n_user')){

      $data = [
        'n_user' => $form_state->getValue('n_user'),
        'p_u_record' =>  $form_state->getValue('p_u_record'),
        'n_u_ranking' =>  $form_state->getValue('n_u_ranking'),
        'p_u_ranking' => $form_state->getValue('p_u_ranking')
      ];

      $this->configFactory->getEditable(static::SETTINGS)
        ->set('config_points_user_week_shopkeeper', $data)
        ->save();
    }

    parent::submitForm($form, $form_state);
  }
}
