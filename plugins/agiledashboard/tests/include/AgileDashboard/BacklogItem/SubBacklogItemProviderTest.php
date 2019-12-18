<?php
/**
 * Copyright (c) Enalean, 2013 - Present. All Rights Reserved.
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
 * along with Tuleap; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

use Tuleap\AgileDashboard\ExplicitBacklog\ArtifactsInExplicitBacklogDao;
use Tuleap\AgileDashboard\ExplicitBacklog\ExplicitBacklogDao;

require_once __DIR__.'/../../../bootstrap.php';

//phpcs:ignore PSR1.Classes.ClassDeclaration.MissingNamespace,Squiz.Classes.ValidClassName.NotCamelCaps
class AgileDashboard_BacklogItem_SubBacklogItemProviderTest extends TuleapTestCase
{
    private $backlog_factory;
    private $backlog_item_collection_factory;
    private $user;

    /**
     * @var \Mockery\LegacyMockInterface|\Mockery\MockInterface|ExplicitBacklogDao
     */
    private $explicit_backlog_dao;

    /**
     * @var \Mockery\LegacyMockInterface|\Mockery\MockInterface|ArtifactsInExplicitBacklogDao
     */
    private $artifact_in_explicit_backlog_dao;

    /**
     * @var AgileDashboard_BacklogItem_SubBacklogItemProvider
     */
    private $provider;

    public function setUp()
    {
        parent::setUp();
        $this->setUpGlobalsMockery();

        $this->backlog_tracker = aTracker()->withId(35)->build();
        $this->task_tracker    = aMockeryTracker()->withId(36)->withParent($this->backlog_tracker)->build();
        $release_tracker       = aMockeryTracker()->withId(105)->build();
        $sprint_tracker        = aMockeryTracker()->withId(106)->build();
        $release_planning      = aPlanning()->withId(1)->withPlanningTracker($release_tracker)->build();
        $sprint_planning       = aPlanning()->withId(2)->withPlanningTracker($sprint_tracker)->withBacklogTracker($this->backlog_tracker)->build();

        $this->milestone       = aMilestone()->withArtifact(anArtifact()->withId(3)->build())->withPlanning($sprint_planning)->build();
        $this->dao             = \Mockery::spy(\Tracker_ArtifactDao::class);

        $this->user                            = aUser()->build();
        $this->backlog_factory                 = \Mockery::spy(\AgileDashboard_Milestone_Backlog_BacklogFactory::class);
        $this->backlog_item_collection_factory = \Mockery::spy(\AgileDashboard_Milestone_Backlog_BacklogItemCollectionFactory::class);
        $this->planning_factory                = \Mockery::spy(PlanningFactory::class);

        $this->explicit_backlog_dao             = Mockery::mock(ExplicitBacklogDao::class);
        $this->artifact_in_explicit_backlog_dao = Mockery::mock(ArtifactsInExplicitBacklogDao::class);

        $this->provider = new AgileDashboard_BacklogItem_SubBacklogItemProvider(
            $this->dao,
            $this->backlog_factory,
            $this->backlog_item_collection_factory,
            $this->planning_factory,
            $this->explicit_backlog_dao,
            $this->artifact_in_explicit_backlog_dao
        );

        $this->planning_factory->shouldReceive('getSubPlannings')->andReturn($sprint_planning);
        $this->planning_factory->shouldReceive('isTrackerIdUsedInAPlanning')->with(35)->andReturn(false);
        $this->planning_factory->shouldReceive('isTrackerIdUsedInAPlanning')->with(36)->andReturn(false);
        $this->planning_factory->shouldReceive('isTrackerIdUsedInAPlanning')->with(105)->andReturn(true);
    }

    public function itReturnsTheMatchingIds()
    {
        stub($this->dao)->getLinkedArtifactsByIds(array(3), array(3))->returnsDar(
            array('id' => 7,  'tracker_id' => 35),
            array('id' => 8,  'tracker_id' => 35),
            array('id' => 11, 'tracker_id' => 35)
        );
        stub($this->dao)->getLinkedArtifactsByIds(array(7, 8, 11), array(3, 7, 8, 11))->returnsEmptyDar();

        $result = $this->provider->getMatchingIds($this->milestone, $this->backlog_tracker, $this->user);

        $this->assertEqual(array(7, 8, 11), array_keys($result));
    }

    public function itReturnsTheMatchingIdsInExplicitTopBacklogContext()
    {
        $milestone           = Mockery::mock(Planning_VirtualTopMilestone::class)->shouldReceive('getArtifactId')->andReturnNull()->getMock();
        $top_backlog_tracker = Mockery::mock(Tracker::class);

        $project = Mockery::mock(Project::class)->shouldReceive('getID')->andReturn(101)->getMock();
        $milestone->shouldReceive('getProject')->andReturn($project);

        $this->explicit_backlog_dao->shouldReceive('isProjectUsingExplicitBacklog')->andReturnTrue();
        $this->artifact_in_explicit_backlog_dao->shouldReceive('getAllTopBacklogItemsForProjectSortedByRank')->andReturn([
            ['artifact_id' => 7],
            ['artifact_id' => 8],
            ['artifact_id' => 11],
        ]);

        $result = $this->provider->getMatchingIds($milestone, $top_backlog_tracker, $this->user);

        $this->assertEqual(array(7, 8, 11), array_keys($result));
    }

    public function itReturnsAnEmptyResultIfThereIsNoMatchingId()
    {
        stub($this->dao)->getLinkedArtifactsByIds()->returnsEmptyDar();

        $result = $this->provider->getMatchingIds($this->milestone, $this->backlog_tracker, $this->user);
        $this->assertEqual([], $result);
    }

    public function itDoesNotFilterFromArtifactsThatAreNotContentOfSubOrCurrentPlanning()
    {
        stub($this->dao)->getLinkedArtifactsByIds(array(3), array(3))->returnsDar(
            array('id' => 7,  'tracker_id' => 35),
            array('id' => 8,  'tracker_id' => 35),
            array('id' => 11, 'tracker_id' => 35),
            array('id' => 158, 'tracker_id' => 105)
        );

        stub($this->dao)->getLinkedArtifactsByIds(array(7, 8, 11), array(3, 7, 8, 11, 158))->returnsEmptyDar();

        $result = $this->provider->getMatchingIds($this->milestone, $this->backlog_tracker, $this->user);
        $this->assertEqual(array(7, 8, 11), array_keys($result));
    }

    public function itFiltersFromArtifactsThatAreChildOfContentOfSubOrCurrentPlanning()
    {
        stub($this->dao)->getLinkedArtifactsByIds(array(3), array(3))->returnsDar(
            array('id' => 7,  'tracker_id' => 35),
            array('id' => 8,  'tracker_id' => 35),
            array('id' => 11, 'tracker_id' => 35),
            array('id' => 158, 'tracker_id' => 105)
        );

        stub($this->dao)->getLinkedArtifactsByIds(array(7, 8, 11), array(3, 7, 8, 11, 158))->returnsDar(
            array('id' => 200,  'tracker_id' => 36),
            array('id' => 201,  'tracker_id' => 36),
            array('id' => 159, 'tracker_id' => 105)
        );

        stub($this->dao)->getLinkedArtifactsByIds(array(200, 201), array(3, 7, 8, 11, 158, 200, 201, 159))->returnsEmptyDar();

        $result = $this->provider->getMatchingIds($this->milestone, $this->task_tracker, $this->user);
        $this->assertEqual(array(200, 201), array_keys($result));
    }
}
