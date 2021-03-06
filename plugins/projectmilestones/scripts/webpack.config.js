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
 *
 */
const path = require("path");
const webpack_configurator = require("../../../tools/utils/scripts/webpack-configurator.js");

const assets_dir_path = path.resolve(
    __dirname,
    "../../../src/www/assets/projectmilestones/scripts"
);
const assets_public_path = "/assets/projectmilestones/scripts/";

const webpack_config = {
    entry: {
        projectmilestones: "./projectmilestones/index.ts"
    },
    context: path.resolve(__dirname),
    output: webpack_configurator.configureOutput(assets_dir_path, assets_public_path),
    resolve: {
        extensions: [".js", ".ts", ".vue"]
    },
    externals: {
        tlp: "tlp"
    },
    module: {
        rules: [
            ...webpack_configurator.configureTypescriptRules(
                webpack_configurator.babel_options_chrome_firefox
            ),
            {
                test: /\.po$/,
                include: /scripts\/charts-builders\/po/,
                use: [{ loader: "json-loader" }, { loader: "po-gettext-loader" }]
            },
            {
                test: /\.po$/,
                include: /projectmilestones\/po\//,
                use: [{ loader: "json-loader" }, { loader: "easygettext-loader" }]
            },
            webpack_configurator.rule_vue_loader
        ]
    },
    plugins: [
        webpack_configurator.getManifestPlugin(),
        webpack_configurator.getVueLoaderPlugin(),
        webpack_configurator.getTypescriptCheckerPlugin(true)
    ],
    resolveLoader: {
        alias: webpack_configurator.easygettext_loader_alias
    }
};

if (process.env.NODE_ENV !== "production") {
    webpack_config.devtool = "cheap-eval-source-map";
}

module.exports = webpack_config;
