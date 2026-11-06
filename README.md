[![Review Assignment Due Date](https://classroom.github.com/assets/deadline-readme-button-22041afd0340ce965d47ae6ef1cefeee28c7c493a6346c4f15d667ab976d596c.svg)](https://classroom.github.com/a/e0vpHYDO)
# GeoWeb

## Repository Overview

A web GIS for exploring annual 30-day stroke mortality rates at healthcare institutions in Portugal. The Leaflet map combines institution markers, separate heatmaps for ischaemic and haemorrhagic strokes, year selection, and regional filters, with PHP endpoints for retrieving and editing records.

The application is in [code/](code/), and the project presentation is available in [WG_presentation.pptx](WG_presentation.pptx).

---

## Controls

| Action | Mouse / On-screen Control |
| --- | --- |
| Pan the map | Click and drag |
| Zoom in / out | Mouse wheel or the zoom slider and its `+` / `-` buttons |
| Change the year | Drag the timeline slider or click a year label |
| Switch the base map or toggle institution / heatmap layers | Layers selector |
| View an institution's details | Click its marker |
| Filter institutions by stroke type, mortality interval, or region | Click the funnel icon and adjust the fields under **FILTROS** |
| Clear the filters | Reset button in the filter sidebar |
| Locate a district | Choose a district under **PROCURA > DISTRITOS** in the sidebar |
| Return to the full Portugal view | Globe icon |
| Add an institution record | Right-click a map location, fill in the popup, and click the hospital icon |
| Save changes to an existing record | Edit the fields in its marker popup, then click the pencil icon |
| Delete an existing record | Click the trash icon in its marker popup |

Record changes require a server configured to allow writes. The included local launcher uses read-only mode by default.
