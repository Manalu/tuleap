<?php
/**
 * Copyright (c) Enalean, 2019. All Rights Reserved.
 *
 * This file is a part of Tuleap.
 *
 * Tuleap is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * Tuleap is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Tuleap. If not, see <http://www.gnu.org/licenses/>.
 */

declare(strict_types=1);

namespace Tuleap\Tracker\Workflow\SimpleMode;

use SimpleXMLElement;
use Transition;
use Workflow;

class SimpleWorkflowXMLExporter
{
    /**
     * @var SimpleWorkflowDao
     */
    private $simple_workflow_dao;

    public function __construct(SimpleWorkflowDao $simple_workflow_dao)
    {
        $this->simple_workflow_dao = $simple_workflow_dao;
    }

    public function exportToXML(Workflow $workflow, SimpleXMLElement $xml_simple_workflow, array $xml_mapping)
    {
        $xml_simple_workflow->addChild('field_id')->addAttribute('REF', array_search($workflow->getFieldId(), $xml_mapping));
        $xml_simple_workflow->addChild('is_used', (string) $workflow->isUsed());

        $this->exportStatesToXML($workflow, $xml_simple_workflow, $xml_mapping);
    }

    private function exportStatesToXML(Workflow $workflow, SimpleXMLElement $xml_simple_workflow, array $xml_mapping)
    {
        $states_sql = $this->simple_workflow_dao->searchStatesForWorkflow((int) $workflow->getId());

        if ($states_sql && count($states_sql) > 0) {
            $states_xml = $xml_simple_workflow->addChild('states');
            foreach ($states_sql as $state_sql) {
                $this->exportStateToXML($states_xml, $xml_mapping, $state_sql['to_id']);
            }
        }
    }

    private function exportStateToXML(SimpleXMLElement $states_xml, array $xml_mapping, int $to_id)
    {
        $state_xml = $states_xml->addChild('state');
        $state_xml->addChild('to_id')->addAttribute('REF', array_search($to_id, $xml_mapping['values']));

        $transitions_xml = $state_xml->addChild('transitions');

        $transitions_sql = $this->simple_workflow_dao->searchTransitionsForState($to_id);

        foreach ($transitions_sql as $transition_sql) {
            $xml_value_field_id =
                $transition_sql['from_id'] === 0 ? Transition::EXPORT_XML_FROM_NEW_VALUE : array_search($transition_sql['from_id'], $xml_mapping['values']);

            $transitions_xml->addChild('transition')
                ->addChild('from_id')
                ->addAttribute('REF', $xml_value_field_id);
        }
    }
}