<!--
  - Copyright (c) Enalean, 2020 - present. All Rights Reserved.
  -
  - This file is a part of Tuleap.
  -
  - Tuleap is free software; you can redistribute it and/or modify
  - it under the terms of the GNU General Public License as published by
  - the Free Software Foundation; either version 2 of the License, or
  - (at your option) any later version.
  -
  - Tuleap is distributed in the hope that it will be useful,
  - but WITHOUT ANY WARRANTY; without even the implied warranty of
  - MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  - GNU General Public License for more details.
  -
  - You should have received a copy of the GNU General Public License
  - along with Tuleap. If not, see <http://www.gnu.org/licenses/>.
  -->

<template>
    <div
        class="project-release-infos-badges"
        v-bind:class="{ 'on-open-sprints-details': open_sprints_details }"
    >
        <div
            class="project-release-badges-open-closed"
            v-bind:class="{ 'open-badges-sprints': open_sprints_details }"
        >
            <release-badges-all-sprints
                v-if="display_badge_all_sprint"
                v-bind:release_data="release_data"
                v-on:onClickOpenSprintsDetails="on_click_open_sprints_details()"
                data-test="badge-sprint"
            />
            <div
                v-if="open_sprints_details"
                class="container-icon-badge-button-to-close"
                data-test="button-to-close-sprint-details"
            >
                <i
                    v-on:click="on_click_close_sprints_details"
                    class="icon-badge-sprint-to-close fa"
                    data-test="button-to-close"
                />
            </div>
            <release-badges-closed-sprints
                v-if="open_sprints_details && user_can_view_sub_milestones_planning"
                v-bind:release_data="release_data"
            />
        </div>
        <hr
            v-if="open_sprints_details"
            data-test="line-displayed"
            class="milestone-badges-sprints-separator"
        />
        <release-others-badges v-bind:release_data="release_data" />
    </div>
</template>

<script lang="ts">
import Vue from "vue";
import { Component, Prop } from "vue-property-decorator";
import { MilestoneData } from "../../../type";
import ReleaseBadgesAllSprints from "./ReleaseBadgesAllSprints.vue";
import ReleaseOthersBadges from "./ReleaseOthersBadges.vue";
import ReleaseBadgesClosedSprints from "./ReleaseBadgesClosedSprints.vue";
import { getTrackerSubmilestoneLabel } from "../../../helpers/tracker-label-helper";
import { openSprintsExist } from "../../../helpers/milestones-sprints-helper";
import { State } from "vuex-class";
@Component({
    components: { ReleaseBadgesClosedSprints, ReleaseOthersBadges, ReleaseBadgesAllSprints }
})
export default class ReleaseBadgesDisplayerIfOpenSprints extends Vue {
    @Prop()
    readonly release_data!: MilestoneData;
    @State
    readonly user_can_view_sub_milestones_planning!: boolean;

    open_sprints_details = false;

    on_click_open_sprints_details(): void {
        this.open_sprints_details = true;
    }

    on_click_close_sprints_details(): void {
        this.open_sprints_details = false;
    }

    get display_badge_all_sprint(): boolean {
        return (
            openSprintsExist(this.release_data) &&
            getTrackerSubmilestoneLabel(this.release_data) !== "" &&
            this.user_can_view_sub_milestones_planning
        );
    }
}
</script>
