using System;
using System.Collections.Generic;
using System.Windows;
using System.Windows.Controls;
using System.Windows.Data;

namespace MpsMonitor.Sdk.Simulator
{
    public static class Helper
    {
        /// <summary>
        /// 
        /// </summary>
        /// <returns></returns>
        internal static List<GridViewColumn> GetGridDevicesColums()
        {
            List<GridViewColumn> devicecolumns = new List<GridViewColumn>
            {
                new GridViewColumn() { Header = "Id", DisplayMemberBinding = new Binding("Id"), Width = 0 },
                new GridViewColumn() { Header = "Serial Number", DisplayMemberBinding = new Binding("SerialNumber") },
                new GridViewColumn() { Header = "Brand", DisplayMemberBinding = new Binding("Product.Brand") },
                new GridViewColumn() { Header = "Model", DisplayMemberBinding = new Binding("Product.Model") },
                new GridViewColumn() { Header = "Mac Address", DisplayMemberBinding = new Binding("MacAddress") },
                new GridViewColumn() { Header = "System Name", DisplayMemberBinding = new Binding("SystemName") },
                new GridViewColumn() { Header = "Firmware", DisplayMemberBinding = new Binding("Firmware") },
                new GridViewColumn() { Header = "Ip Address", DisplayMemberBinding = new Binding("IpAddress") }
            };

            return devicecolumns;
        }


        internal static List<GridViewColumn> GetGridOfficesColums()
        {
            List<GridViewColumn> officeColumns = new List<GridViewColumn>
            {
                new GridViewColumn() { Header = "Id", DisplayMemberBinding = new Binding("Id"), Width = 0 },
                new GridViewColumn() { Header = "Code", DisplayMemberBinding = new Binding("Code") },
                new GridViewColumn() { Header = "Description", DisplayMemberBinding = new Binding("Description") },
                new GridViewColumn() { Header = "Enable Auto Assign", DisplayMemberBinding = new Binding("EnableAutoAssign") },
                new GridViewColumn() { Header = "Address", DisplayMemberBinding = new Binding("Address") },
                new GridViewColumn() { Header = "City", DisplayMemberBinding = new Binding("City") },
                new GridViewColumn() { Header = "ZipCode", DisplayMemberBinding = new Binding("ZipCode") },
                new GridViewColumn() { Header = "Province", DisplayMemberBinding = new Binding("Province") },
                new GridViewColumn() { Header = "Country", DisplayMemberBinding = new Binding("Country") },
                new GridViewColumn() { Header = "Telephone", DisplayMemberBinding = new Binding("Telephone") },
                new GridViewColumn() { Header = "Fax", DisplayMemberBinding = new Binding("Fax") },
                new GridViewColumn() { Header = "Note", DisplayMemberBinding = new Binding("Note") }
            };


            return officeColumns;
        }

        internal static List<GridViewColumn> GetGridOfficesSubnetColums()
        {
            List<GridViewColumn> officeSubnetColumns = new List<GridViewColumn>
            {
                new GridViewColumn() { Header = "Id", DisplayMemberBinding = new Binding("Id"), Width = 0 },
                new GridViewColumn() { Header = "IpRootFrom", DisplayMemberBinding = new Binding("IpRootFrom") },
                new GridViewColumn() { Header = "IpRootTo", DisplayMemberBinding = new Binding("IpRootTo") },
            };


            return officeSubnetColumns;
        }

        internal static List<GridViewColumn> GetGridDataDetectionColums(RoutedEventHandler delete_Click, RoutedEventHandler edit_Click)
        {
            List<GridViewColumn> dataDetectionColumns = new List<GridViewColumn>();
            dataDetectionColumns.Add(new GridViewColumn() { Header = "Identifier", DisplayMemberBinding = new Binding("Identifier"), Width = 0 });
            dataDetectionColumns.Add(new GridViewColumn() { Header = "Occurence", DisplayMemberBinding = new Binding("Occurence") });
            dataDetectionColumns.Add(new GridViewColumn() { Header = "Start At UTC", DisplayMemberBinding = new Binding("StartAt") });
            dataDetectionColumns.Add(new GridViewColumn() { Header = "Days", DisplayMemberBinding = new Binding("Days") });
            dataDetectionColumns.Add(new GridViewColumn() { Header = "TimeZone", DisplayMemberBinding = new Binding("TimeZone") });
            dataDetectionColumns.Add(new GridViewColumn() { Header = "LastRequest", DisplayMemberBinding = new Binding("LastRequest") });


            GridViewColumn editColumn = new GridViewColumn();
            editColumn.Header = "Edit";
            FrameworkElementFactory editButton = new FrameworkElementFactory(typeof(Button), string.Format("{0}Button", "edit".ToLower()));
            editButton.SetValue(Button.ContentProperty, "Edit");
            editButton.SetValue(Button.NameProperty, string.Format("{0}Button", "edit".ToLower()));
            editButton.AddHandler(Button.ClickEvent, edit_Click);
            DataTemplate cellTemplateEdit = new DataTemplate();
            cellTemplateEdit.VisualTree = editButton;
            editColumn.CellTemplate = cellTemplateEdit;
            dataDetectionColumns.Add(editColumn);

            GridViewColumn deleteColumn = new GridViewColumn();
            deleteColumn.Header = "Delete";
            FrameworkElementFactory deleteButton = new FrameworkElementFactory(typeof(Button), string.Format("{0}Button", "delete".ToLower()));
            deleteButton.SetValue(Button.ContentProperty, "Delete");
            deleteButton.SetValue(Button.NameProperty, string.Format("{0}Button", "delete".ToLower()));
            deleteButton.AddHandler(Button.ClickEvent, delete_Click);
            DataTemplate cellTemplate = new DataTemplate();
            cellTemplate.VisualTree = deleteButton;
            deleteColumn.CellTemplate = cellTemplate;
            dataDetectionColumns.Add(deleteColumn);

            return dataDetectionColumns;
        }

        internal static List<GridViewColumn> GetGridNetworkColums(RoutedEventHandler delete_Click, RoutedEventHandler edit_Click)
        {
            List<GridViewColumn> networkColumns = new List<GridViewColumn>
            {
                new GridViewColumn() { Header = "Identifier", DisplayMemberBinding = new Binding("Identifier"), Width = 0 },
                new GridViewColumn() { Header = "Office Id", DisplayMemberBinding = new Binding("OfficeId"), },
                new GridViewColumn() { Header = "Office Code", DisplayMemberBinding = new Binding("OfficeCode") },
                new GridViewColumn() { Header = "Office Description", DisplayMemberBinding = new Binding("OfficeDescription") },
                new GridViewColumn() { Header = "Subnet Mask", DisplayMemberBinding = new Binding("SubnetMask") },
                new GridViewColumn() { Header = "PartialWalk OID", DisplayMemberBinding = new Binding("PartialWalkOID") },
                new GridViewColumn() { Header = "Ip Start", DisplayMemberBinding = new Binding("IpStart") },
                new GridViewColumn() { Header = "Ip End", DisplayMemberBinding = new Binding("IpEnd") }
            };
            GridViewColumn editColumn = new GridViewColumn();
            editColumn.Header = "Edit";
            FrameworkElementFactory editButton = new FrameworkElementFactory(typeof(Button), string.Format("{0}Button", "edit".ToLower()));
            editButton.SetValue(Button.ContentProperty, "Edit");
            editButton.SetValue(Button.NameProperty, string.Format("{0}Button", "edit".ToLower()));
            editButton.AddHandler(Button.ClickEvent, edit_Click);
            DataTemplate cellTemplateEdit = new DataTemplate();
            cellTemplateEdit.VisualTree = editButton;
            editColumn.CellTemplate = cellTemplateEdit;
            networkColumns.Add(editColumn);

            GridViewColumn deleteColumn = new GridViewColumn();
            deleteColumn.Header = "Delete";
            FrameworkElementFactory deleteButton = new FrameworkElementFactory(typeof(Button), string.Format("{0}Button", "delete".ToLower()));
            deleteButton.SetValue(Button.ContentProperty, "Delete");
            deleteButton.SetValue(Button.NameProperty, string.Format("{0}Button", "delete".ToLower()));
            deleteButton.AddHandler(Button.ClickEvent, delete_Click);
            DataTemplate cellTemplate = new DataTemplate();
            cellTemplate.VisualTree = deleteButton;
            deleteColumn.CellTemplate = cellTemplate;
            networkColumns.Add(deleteColumn);

            
            return networkColumns;

        }

        internal static List<GridViewColumn> GetGridConnectorsColums()
        {
            List<GridViewColumn> connectorColumns = new List<GridViewColumn>
            {
                new GridViewColumn() { Header = "Identifier", DisplayMemberBinding = new Binding("Identifier"), Width = 0 },
                new GridViewColumn() { Header = "IP", DisplayMemberBinding = new Binding("IP") },
                new GridViewColumn() { Header = "Customer Code", DisplayMemberBinding = new Binding("CustomerCode") },
                new GridViewColumn() { Header = "Customer Description", DisplayMemberBinding = new Binding("CustomerDescription") },
                new GridViewColumn() { Header = "Automatic Update", DisplayMemberBinding = new Binding("AutomaticUpdate") },

                new GridViewColumn() { Header = "System Name", DisplayMemberBinding = new Binding("SystemName") },
                new GridViewColumn() { Header = "Version", DisplayMemberBinding = new Binding("Version") },
                new GridViewColumn() { Header = "Last Run", DisplayMemberBinding = new Binding("LastRun") },
                new GridViewColumn() { Header = "Last Ping", DisplayMemberBinding = new Binding("LastPing") },
                new GridViewColumn() { Header = "Last Upload", DisplayMemberBinding = new Binding("LastUpload") },
                new GridViewColumn() { Header = "Service Build Number", DisplayMemberBinding = new Binding("ServiceBuildNumber") }
            };


            return connectorColumns;
        }

        internal static List<GridViewColumn> GetGridAlertColums()
        {
            List<GridViewColumn> alertColumns = new List<GridViewColumn>
            {
                new GridViewColumn() { Header = "Id", DisplayMemberBinding = new Binding("Id"), Width = 0 },
                new GridViewColumn() { Header = "Customer", DisplayMemberBinding = new Binding("CustomerDescription") },
                new GridViewColumn() { Header = "Serial Number", DisplayMemberBinding = new Binding("SerialNumber") },
                new GridViewColumn() { Header = "Ip Address", DisplayMemberBinding = new Binding("IpAddress") },
                new GridViewColumn() { Header = "Asset Number", DisplayMemberBinding = new Binding("AssetNumber") },
                new GridViewColumn() { Header = "Brand", DisplayMemberBinding = new Binding("ProductBrand") },
                new GridViewColumn() { Header = "Model", DisplayMemberBinding = new Binding("ProductModel") },
                new GridViewColumn() { Header = "Warning", DisplayMemberBinding = new Binding("Warning") },
                new GridViewColumn() { Header = "Office", DisplayMemberBinding = new Binding("Office.Description") },
                new GridViewColumn() { Header = "Project", DisplayMemberBinding = new Binding("Project.Description") },
                new GridViewColumn() { Header = "Last Update", DisplayMemberBinding = new Binding("ActualDate") },
                new GridViewColumn() { Header = "Installed", DisplayMemberBinding = new Binding("TonerInstalled") },
                new GridViewColumn() { Header = "Cancelled", DisplayMemberBinding = new Binding("CancelledOn") },
                new GridViewColumn() { Header = "Hidden", DisplayMemberBinding = new Binding("IsHidden") },
                new GridViewColumn() { Header = "Delivered", DisplayMemberBinding = new Binding("ShippedSupplyCreation") },
                new GridViewColumn() { Header = "Generation Type", DisplayMemberBinding = new Binding("ShippedSupplyGenerationType") },
                new GridViewColumn() { Header = "Initial Level %", DisplayMemberBinding = new Binding("InitialResidualPercentage") },
                new GridViewColumn() { Header = "Last Level %", DisplayMemberBinding = new Binding("ActualResidualPercentage") },
                new GridViewColumn() { Header = "Exhausted Expiration", DisplayMemberBinding = new Binding("ExhaustedExpiration") },
                new GridViewColumn() { Header = "Exhausted Expiration", DisplayMemberBinding = new Binding("ExhaustedExpiration") },
                new GridViewColumn() { Header = "Suggested consumable", DisplayMemberBinding = new Binding("SuggestedPartNumber") }
            };
            
            var multi = new MultiBinding();
            multi.Bindings.Add(new Binding("SupplyType"));
            multi.Bindings.Add(new Binding("ColorType"));
            multi.StringFormat = "{0} \n {1}";
            alertColumns.Add(new GridViewColumn() { Header = "Type", DisplayMemberBinding = multi });
            return alertColumns;
        }

        /// <summary>
        /// 
        /// </summary>
        /// <returns></returns>
        internal static List<GridViewColumn> GetGridShippedColumns()
        {
            List<GridViewColumn> shippedColumns = new List<GridViewColumn>
            {
                new GridViewColumn() { Header = "Id", DisplayMemberBinding = new Binding("Id"), Width = 0 },
                new GridViewColumn() { Header = "Customer", DisplayMemberBinding = new Binding("CustomerDescription") },
                new GridViewColumn() { Header = "Serial Number", DisplayMemberBinding = new Binding("SerialNumber") },
                new GridViewColumn() { Header = "Asset Number", DisplayMemberBinding = new Binding("AssetNumber") },
                new GridViewColumn() { Header = "Brand", DisplayMemberBinding = new Binding("Product.Brand") },
                new GridViewColumn() { Header = "Model", DisplayMemberBinding = new Binding("Product.Model") },

                new GridViewColumn() { Header = "Warning", DisplayMemberBinding = new Binding("Supply.Warning") },
                new GridViewColumn() { Header = "Creation", DisplayMemberBinding = new Binding("Creation") },
                new GridViewColumn() { Header = "Office", DisplayMemberBinding = new Binding("Office.Description") },
                new GridViewColumn() { Header = "Project", DisplayMemberBinding = new Binding("Project.Description") },
                new GridViewColumn() { Header = "Document Number", DisplayMemberBinding = new Binding("DocumentNumber") },
                new GridViewColumn() { Header = "Part Number", DisplayMemberBinding = new Binding("PartNumber") },
                new GridViewColumn() { Header = "Order Number", DisplayMemberBinding = new Binding("OrderNumber") },
                new GridViewColumn() { Header = "Generation", DisplayMemberBinding = new Binding("Generation") }
            };

            var multi = new MultiBinding();
            multi.Bindings.Add(new Binding("Supply.SupplyType"));
            multi.Bindings.Add(new Binding("Supply.ColorType"));
            multi.StringFormat = "{0} \n {1}";

            shippedColumns.Add(new GridViewColumn() { Header = "Type", DisplayMemberBinding = multi });
            return shippedColumns;
        }

        /// <summary>
        /// 
        /// </summary>
        /// <returns></returns>
        internal static List<GridViewColumn> GetGridSuppliesColums()
        {
            List<GridViewColumn> suppliesColumns = new List<GridViewColumn>
            {
                new GridViewColumn() { Header = "Id", DisplayMemberBinding = new Binding("Id"), Width = 0 },
                new GridViewColumn() { Header = "Part Number", DisplayMemberBinding = new Binding("PartNumber") },
                new GridViewColumn() { Header = "Description", DisplayMemberBinding = new Binding("Description") },
                new GridViewColumn() { Header = "Type", DisplayMemberBinding = new Binding("SupplyType") },
                new GridViewColumn() { Header = "Color", DisplayMemberBinding = new Binding("ColorType") },
                new GridViewColumn() { Header = "MaintenanceKitType", DisplayMemberBinding = new Binding("MaintenanceKitType.Description") },
                new GridViewColumn() { Header = "MaintenanceKitColor", DisplayMemberBinding = new Binding("MaintenanceKitColor.Description") },
                new GridViewColumn() { Header = "Duration", DisplayMemberBinding = new Binding("Duration") }
            };

            return suppliesColumns;
        }

        /// <summary>
        /// 
        /// </summary>
        /// <returns></returns>
        internal static List<GridViewColumn> GetGridSdsServiceRequestColums()
        {
            List<GridViewColumn> sdsServiceRequestColumns = new List<GridViewColumn>
            {
                new GridViewColumn() { Header = "Id", DisplayMemberBinding = new Binding("Id"), Width = 0 },
                new GridViewColumn() { Header = "Customer", DisplayMemberBinding = new Binding("CustomerDescription") },
                new GridViewColumn() { Header = "Serial Number", DisplayMemberBinding = new Binding("InstalledProductSerialNumber") },
                new GridViewColumn() { Header = "Model", DisplayMemberBinding = new Binding("Model") },
                new GridViewColumn() { Header = "Code", DisplayMemberBinding = new Binding("Code") },
                new GridViewColumn() { Header = "Context", DisplayMemberBinding = new Binding("EventCodeContext") },
                new GridViewColumn() { Header = "Date", DisplayMemberBinding = new Binding("ActionDateUtc") },
                new GridViewColumn() { Header = "Severity", DisplayMemberBinding = new Binding("Severity") },
                new GridViewColumn() { Header = "State", DisplayMemberBinding = new Binding("CurrentState") },
                new GridViewColumn() { Header = "Title", DisplayMemberBinding = new Binding("Title") },
                new GridViewColumn() { Header = "Action Type", DisplayMemberBinding = new Binding("ActionType") },
                new GridViewColumn() { Header = "Total Impressions", DisplayMemberBinding = new Binding("TotalImpressions") },
                new GridViewColumn() { Header = "Firmware", DisplayMemberBinding = new Binding("FirmwareVersion") }
            };

            return sdsServiceRequestColumns;

        }

        internal static List<GridViewColumn> GetGridConfigurationsColums()
        {
            List<GridViewColumn> configurationColumns = new List<GridViewColumn>
            {
                new GridViewColumn() { Header = "Id", DisplayMemberBinding = new Binding("Id"), Width = 0 },
                new GridViewColumn() { Header = "Description", DisplayMemberBinding = new Binding("Description") },
                new GridViewColumn() { Header = "Explorer Data SystemName", DisplayMemberBinding = new Binding("ExplorerDataSystemName") },
                new GridViewColumn() { Header = "Is Valid", DisplayMemberBinding = new Binding("IsValidConfiguration") },
                new GridViewColumn() { Header = "Is Enable", DisplayMemberBinding = new Binding("IsEnable") },
                new GridViewColumn() { Header = "Use Auto Assign", DisplayMemberBinding = new Binding("UseAutoAssign") },
                new GridViewColumn() { Header = "Explorer Data Id", DisplayMemberBinding = new Binding("ExplorerDataId") },
                new GridViewColumn() { Header = "Customer Id", DisplayMemberBinding = new Binding("CustomerId") }
            };

            return configurationColumns;
        }
    }
}
