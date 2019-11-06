<?php
/**
 * Copyright (c) Enalean, 2019-Present. All Rights Reserved.
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
 *
 */

declare(strict_types=1);

namespace Tuleap\FRS\LicenseAgreement\Admin;

use HTTPRequest;
use Project;
use Tuleap\FRS\FRSPermissionManager;
use Tuleap\FRS\ToolbarPresenter;
use Tuleap\Layout\BaseLayout;
use Tuleap\Request\DispatchableWithProject;
use Tuleap\Request\DispatchableWithRequest;
use Tuleap\Request\ForbiddenException;
use Tuleap\Request\NotFoundException;

class ListLicenseAgreementsController implements DispatchableWithRequest, DispatchableWithProject
{
    /**
     * @var \ProjectManager
     */
    private $project_manager;
    /**
     * @var \TemplateRendererFactory
     */
    private $renderer_factory;
    /**
     * @var FRSPermissionManager
     */
    private $permission_manager;

    public function __construct(\ProjectManager $project_manager, \TemplateRendererFactory $renderer_factory, FRSPermissionManager $permission_manager)
    {
        $this->project_manager  = $project_manager;
        $this->renderer_factory = $renderer_factory;
        $this->permission_manager = $permission_manager;
    }

    public function getProject(array $variables): Project
    {
        $project = $this->project_manager->getProject($variables['id']);
        if ($project && ! $project->isError()) {
            return $project;
        }
        throw new NotFoundException();
    }

    /**
     * Is able to process a request routed by FrontRouter
     *
     * @param HTTPRequest $request
     * @param BaseLayout  $layout
     * @param array       $variables
     * @return void
     * @throws ForbiddenException
     * @throws NotFoundException
     */
    public function process(HTTPRequest $request, BaseLayout $layout, array $variables)
    {
        $project = $this->getProject($variables);

        if (! $this->permission_manager->isAdmin($project, $request->getCurrentUser())) {
            throw new ForbiddenException('Only for files administrators');
        }

        $title = _('Files Administration');
        $toolbar_presenter = new ToolbarPresenter($project);

        $toolbar_presenter->setLicenseAgreementIsActive();
        $toolbar_presenter->displaySectionNavigation();

        $file_service = $project->getFileService();
        if (! $file_service) {
            throw new NotFoundException('Service is not active for this project');
        }
        $file_service->displayFRSHeader($project, $title);
        $header_renderer = $this->renderer_factory->getRenderer(__DIR__ . '/../../../../templates/frs');
        $header_renderer->renderToPage('toolbar-presenter', $toolbar_presenter);
    }

    public static function getUrl(Project $project): string
    {
        return sprintf('/file/%d/admin/license-agreements', $project->getID());
    }
}