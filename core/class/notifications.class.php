<?php

/* This file is part of Jeedom.
 *
 * Jeedom is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Jeedom is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
 */

  require_once __DIR__  . '/../../../../core/php/core.inc.php';

  class notifications extends eqLogic
  {
    
    // Fonction exécutée automatiquement avant la création de l'équipement
      //
      public function preInsert()
      {
      }

      // Fonction exécutée automatiquement après la création de l'équipement
      //
      public function postInsert()
      {
      }

      // Fonction exécutée automatiquement avant la mise à jour de l'équipement
      //
      public function preUpdate()
      {
      }

      // Fonction exécutée automatiquement après la mise à jour de l'équipement
      //
      public function postUpdate()
      {
      }

      // Fonction exécutée automatiquement avant la sauvegarde (création ou mise à jour) de l'équipement
      //
      public function preSave()
      {
      }

      // Fonction exécutée automatiquement après la sauvegarde (création ou mise à jour) de l'équipement
      //
      public function postSave()
      {
          $obj = $this->getCmd(null, 'notify');
          if (!is_object($obj)) {
              $obj = new notificationsCmd();
              $obj->setName(__('Notification', __FILE__));
          }
          $obj->setEqLogic_id($this->getId());
          $obj->setLogicalId('notify');
          $obj->setType('action');
          $obj->setSubType('message');
          $obj->save();
      }

      // Fonction exécutée automatiquement avant la suppression de l'équipement
      //
      public function preRemove()
      {
      }

      // Fonction exécutée automatiquement après la suppression de l'équipement
      //
      public function postRemove()
      {
      }

      // Envoyer les notifications
      //
      public function envoyerNotifications($titre, $message)
      {
          foreach ($this->getConfiguration('consigne_conf') as $action) {
              try {
                  $cmd = cmd::byId(str_replace('#', '', $action['cmd']));
                  if (!is_object($cmd)) {
                      continue;
                  }
                  $options = array();
                  if (isset($action['options'])) {
                      $options = $action['options'];
                      if (isset($options['title'])) {
                          $tit = trim($options['title']);
                          if ($tit == '') {
                              $tit = $titre;
                          } else {
                            $tit = str_replace('#title#', $titre, $tit);
                          }
                          $options['title'] = $tit;
                      }
                      if (isset($options['message'])) {
                          $options['message'] = $message;
                      }
                  }
                  scenarioExpression::createAndExec('action', $action['cmd'], $options);
              } catch (Exception $e) {
                  log::add('vueThermostat', 'error', $this->getHumanName() . __(' : Erreur lors de l\'éxecution de ', __FILE__) . $action['cmd'] . __('. Détails : ', __FILE__) . $e->getMessage());
              }
          }
      }
  }
  
  class notificationsCmd extends cmd
  {
      // Exécution d'une commande
      //
      public function execute($_options = array())
      {
          $eqLogic = $this->getEqLogic();
        
          if ($this->getType() === 'action') {
              if ($this->getSubType() === 'message') {
                  if ($_options !== null) {
                      $titre = '';
                      if (isset($_options['title'])) {
                          $titre = $_options['title'];
                      }
                      $message = '';
                      if (isset($_options['message'])) {
                          $message = $_options['message'];
                      }
                      $eqLogic->EnvoyerNotifications($titre, $message);
                  }
              }
          }
      }
  }
