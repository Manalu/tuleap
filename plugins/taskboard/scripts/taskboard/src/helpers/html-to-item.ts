/*
 * Copyright (c) Enalean, 2019 - present. All Rights Reserved.
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

import { Card, ColumnDefinition, Swimlane } from "../type";

export function hasCardBeenDroppedInTheSameCell(
    target_cell: HTMLElement,
    source_cell: HTMLElement
): boolean {
    return (
        target_cell.dataset.columnId === source_cell.dataset.columnId &&
        target_cell.dataset.swimlaneId === source_cell.dataset.swimlaneId
    );
}

export function getCardFromSwimlane(swimlane: Swimlane, card_element?: HTMLElement): Card | null {
    if (!card_element) {
        return null;
    }

    const card = swimlane.children_cards.find(
        card => card.id === Number(card_element.dataset.cardId)
    );

    return card || null;
}

export function getColumnAndSwimlaneFromCell(
    swimlanes: Swimlane[],
    columns: ColumnDefinition[],
    cell: HTMLElement
): {
    swimlane?: Swimlane;
    column?: ColumnDefinition;
} {
    const swimlane = swimlanes.find(
        swimlane => swimlane.card.id === Number(cell.dataset.swimlaneId)
    );
    const column = columns.find(column => column.id === Number(cell.dataset.columnId));

    return {
        swimlane,
        column
    };
}