using MpsMonitor.Sdk.Models.Enums;
using System;
using System.Collections.Generic;
using System.ComponentModel.DataAnnotations;
using System.Runtime.Serialization;
using System.Text;

namespace MpsMonitor.Sdk.Models.Dto
{
    /// <summary>
    /// The jam device
    /// </summary>
    /// <seealso cref="EntityDto" />
    [DataContract]
    public class SdsDeviceBaseDto : EntityDto
    {
        /// <summary>
        /// Gets or sets the jam device id
        /// </summary>
        [DataMember]
        public int? JamDeviceId { get; set; }

        /// <summary>
        /// Gets or sets the jam hostname.
        /// </summary>
        [DataMember]
        public string HostName { get; set; }

        /// <summary>
        /// Gets or sets the serial number.
        /// </summary>
        /// <value>
        /// The serial number.
        /// </value>
        [DataMember]
        public string SerialNumber { get; set; }

        /// <summary>
        /// Gets or sets the name of the model.
        /// </summary>
        /// <value>
        /// The name of the model.
        /// </value>
        [DataMember]
        public string ModelName { get; set; }

        /// <summary>
        /// Gets or sets the mac address.
        /// </summary>
        /// <value>
        /// The mac address.
        /// </value>
        [DataMember]
        public string MacAddress { get; set; }

        /// <summary>
        /// Gets or sets the ip address.
        /// </summary>
        /// <value>
        /// The ip address.
        /// </value>
        [DataMember]
        public string IpAddress { get; set; }

        /// <summary>
        /// Get if it is an USB device
        /// </summary>
        [DataMember]
        public bool IsUsbDevice { get; set; }
    }
    /// <summary>
    /// SdsDeviceListDto
    /// </summary>
    [DataContract]
    public class SdsDeviceListDto : SdsDeviceBaseDto
    {
        /// <summary>
        /// LastTime JAMC contacted the printer
        /// </summary>
        [DataMember]
        public DateTime? LastContactTimeUtc { get; set; }

        /// <summary>
        /// LastTime eXplorerJam contacted the printer
        /// </summary>
        [DataMember]
        public DateTime? LastMpsContactTimeUtc { get; set; }

        [DataMember]
        public string MskuAttributeState { get; set; }
        [DataMember]
        public bool HasGenuineHpCartridges { get; set; }

        [DataMember]
        public string GenuineHpCartridgeStatus { get; set; }
        [DataMember]
        public bool IsHpManagedDevice { get; set; }
        [DataMember]
        public DateTime? HpManagedAuthorizationExpirationUtc { get; set; }
        [DataMember]
        public DateTime? EnhancedAccessExpirationUtc { get; set; }
        [DataMember]
        public bool HasEarlyReplacement { get; set; }
        [DataMember]
        public bool HasOpenCommonActions { get; set; }
        [DataMember]
        public bool HasOpenPredictiveActions { get; set; }
        public string FirmawareVersionUpgradeAvailable { get; set; }

    }

    /// <summary>
    /// The jam device
    /// </summary>
    /// <seealso cref="EntityDto" />
    [DataContract]
    public class SdsDeviceDto : SdsDeviceBaseDto
    {
        /// <summary>
        /// Initializes a new instance of the <see cref="SdsDeviceDto"/> class.
        /// </summary>
        public SdsDeviceDto()
        {
            this.Firmwares = new List<SdsDeviceFirmwareImageInfoDto>();
            this.Supplies = new List<SdsDeviceSupplyDto>();
            this.LastConfigItems = new List<SdsConfigItemDto>();
            this.RelevantEventlogs = new List<SdsDeviceEventDto>();
            this.Actions = new List<SdsDeviceActionDto>();
        }

        /// <summary>
        /// LastTime JAMC contacted the printer
        /// </summary>
        [DataMember]
        public DateTime? LastContactTimeUtc { get; set; }

        /// <summary>
        /// LastTime eXplorerJam contacted the printer
        /// </summary>
        [DataMember]
        public DateTime? LastMpsContactTimeUtc { get; set; }

        /// <summary>
        /// Gets or sets the last scan time UTC.
        /// </summary>
        /// <value>
        /// The last scan time UTC.
        /// </value>
        [DataMember]
        public DateTime? LastScanTimeUtc { get; set; }

        /// <summary>
        /// Gets or sets the last operations scan time UTC.
        /// </summary>
        /// <value>
        /// The last operations scan time UTC.
        /// </value>
        [DataMember]
        public DateTime? LastOperationsScanTimeUtc { get; set; }

        /// <summary>
        /// Gets or sets the last events scan time UTC.
        /// </summary>
        /// <value>
        /// The last events scan time UTC.
        /// </value>
        [DataMember]
        public DateTime? LastEventsScanTimeUtc { get; set; }

        /// <summary>
        /// Gets or sets a value indicating whether this instance is hp managed device.
        /// </summary>
        /// <value>
        ///   <c>true</c> if this instance is hp managed device; otherwise, <c>false</c>.
        /// </value>
        [DataMember]
        public bool IsHpManagedDevice { get; set; }

        /// <summary>
        /// Gets or sets the genuine hp cartridge status.
        /// </summary>
        /// <value>
        /// The genuine hp cartridge status.
        /// </value>
        [DataMember]
        public string GenuineHPCartridgeStatus { get; set; }

        /// <summary>
        /// Gets if the device has genuine hp cartridge installed.
        /// </summary>
        [DataMember]
        public bool HasGenuineHpCartridges { get; set; }

        /// <summary>
        /// Gets or sets the hp managed authorization expiration UTC.
        /// </summary>
        /// <value>
        /// The hp managed authorization expiration UTC.
        /// </value>
        [DataMember]
        public DateTime? HpManagedAuthorizationExpirationUtc { get; set; }

        /// <summary>
        /// Gets or sets the ews credential result.
        /// </summary>
        /// <value>
        /// The ews credential result.
        /// </value>
        [DataMember]
        public JamOperationResultEnum? EwsCredentialResult { get; set; }

        /////// <summary>
        /////// Gets or sets the last collection time UTC.
        /////// </summary>
        /////// <value>
        /////// The last collection time UTC.
        /////// </value>
        ////[DataMember]
        ////public DateTime? LastCollectionTimeUtc { get; set; }

        /// <summary>
        /// Gets or sets the firmware version.
        /// </summary>
        /// <value>
        /// The firmware version.
        /// </value>
        [DataMember]
        public string FirmwareVersion { get; set; }

        /// <summary>
        /// Gets or sets the firmware date code UTC.
        /// </summary>
        /// <value>
        /// The firmware date code UTC.
        /// </value>
        [DataMember]
        public DateTime? FirmwareDateCodeUtc { get; set; }

        /// <summary>
        /// Gets or sets the firmaware version upgrade available.
        /// </summary>
        /// <value>
        /// The firmaware version upgrade available.
        /// </value>
        [DataMember]
        public string FirmawareVersionUpgradeAvailable { get; set; }

        /// <summary>
        /// Gets or sets a value indicating whether [customer jam enabled].
        /// </summary>
        /// <value>
        ///   <c>true</c> if [customer jam enabled]; otherwise, <c>false</c>.
        /// </value>
        [DataMember]
        public bool CustomerJamEnabled { get; set; }

        /// <summary>
        /// Gets or sets a value indicating whether [dealer jam enabled].
        /// </summary>
        /// <value>
        ///   <c>true</c> if [dealer jam enabled]; otherwise, <c>false</c>.
        /// </value>
        [DataMember]
        public bool DealerJamEnabled { get; set; }

        /// <summary>
        /// Gets or sets a value indicating whether this instance is inside project.
        /// </summary>
        /// <value>
        ///   <c>true</c> if this instance is inside project; otherwise, <c>false</c>.
        /// </value>
        [DataMember]
        public bool IsInsideProject { get; set; }

        /// <summary>
        /// Gets or sets the installed product events count.
        /// </summary>
        /// <value>
        /// The installed product events count.
        /// </value>
        [DataMember]
        public int InstalledProductEventsCount { get; set; }

        /// <summary>
        /// Gets or sets the installed product pending operations count.
        /// </summary>
        /// <value>
        /// The installed product pending operations count.
        /// </value>
        [DataMember]
        public int InstalledProductPendingOperationsCount { get; set; }

        /// <summary>
        /// Gets or sets the enhanced access expiration UTC.
        /// </summary>
        /// <value>
        /// The enhanced access expiration UTC.
        /// </value>
        [DataMember]
        public DateTime? EnhancedAccessExpirationUtc { get; set; }

        /// <summary>
        /// Gets or sets the operation results.
        /// </summary>
        /// <value>
        /// The operation results.
        /// </value>
        [DataMember]
        public string OperationResults { get; set; }

        /// <summary>
        /// Gets or sets the supported features.
        /// </summary>
        /// <value>
        /// The supported features.
        /// </value>
        [DataMember]
        public string SupportedFeatures { get; set; }

        /// <summary>
        /// Gets or sets the status.
        /// </summary>
        /// <value>
        /// The status.
        /// </value>
        [DataMember]
        public InstalledProductJamStatusEnum Status { get; set; }

        /// <summary>
        /// Gets or sets the cartridge status.
        /// </summary>
        /// <value>
        /// The cartridge status.
        /// </value>
        [DataMember]
        public InstalledProductJamStatusEnum CartridgeStatus { get; set; }

        /// <summary>
        /// Gets or sets the managed status.
        /// </summary>
        /// <value>
        /// The managed status.
        /// </value>
        [DataMember]
        public InstalledProductJamStatusEnum ManagedStatus { get; set; }

        /// <summary>
        /// Gets or sets the firmwares.
        /// </summary>
        /// <value>
        /// The firmwares.
        /// </value>
        [DataMember]
        public IEnumerable<SdsDeviceFirmwareImageInfoDto> Firmwares { get; set; }

        /// <summary>
        /// Gets or sets the supplies.
        /// </summary>
        /// <value>
        /// The supplies.
        /// </value>
        [DataMember]
        public IEnumerable<SdsDeviceSupplyDto> Supplies { get; set; }

        /// <summary>
        /// Gets or sets a value indicating whether this instance can power cycle reset.
        /// </summary>
        /// <value>
        ///   <c>true</c> if this instance can power cycle reset; otherwise, <c>false</c>.
        /// </value>
        [DataMember]
        public bool CanPowerCycleReset { get; set; }

        /// <summary>
        /// Gets or sets a value indicating whether this instance can set configuration items.
        /// </summary>
        /// <value>
        ///   <c>true</c> if this instance can set configuration items; otherwise, <c>false</c>.
        /// </value>
        [DataMember]
        public bool CanSetConfigItems { get; set; }

        /// <summary>
        /// Gets or sets a value indicating whether this instance can get configuration items.
        /// </summary>
        /// <value>
        ///   <c>true</c> if this instance can get configuration items; otherwise, <c>false</c>.
        /// </value>
        [DataMember]
        public bool CanGetConfigItems { get; set; }

        /// <summary>
        /// Gets or sets a value indicating whether this instance can fw upgrade.
        /// </summary>
        /// <value>
        ///   <c>true</c> if this instance can fw upgrade; otherwise, <c>false</c>.
        /// </value>
        [DataMember]
        public bool CanFWUpgrade { get; set; }

        /// <summary>
        /// Gets or sets a value indicating whether [enable remote ews].
        /// </summary>
        /// <value>
        ///   <c>true</c> if [enable remote ews]; otherwise, <c>false</c>.
        /// </value>
        [DataMember]
        public bool? EnableRemoteEws { get; set; }

        /// <summary>
        /// Gets or sets a value indicating whether this instance can remote ews.
        /// </summary>
        /// <value>
        ///   <c>true</c> if this instance can remote ews; otherwise, <c>false</c>.
        /// </value>
        [DataMember]
        public bool CanRemoteEWS { get; set; }

        /// <summary>
        /// Gets or sets a value indicating whether this instance can training on demand.
        /// </summary>
        /// <value>
        ///   <c>true</c> if this instance can training on demand; otherwise, <c>false</c>.
        /// </value>
        [DataMember]
        public bool CanTrainingOnDemand { get; set; }

        /// <summary>
        /// Gets or sets a value indicating whether this instance can view actions.
        /// </summary>
        /// <value>
        ///   <c>true</c> if this instance can view actions; otherwise, <c>false</c>.
        /// </value>
        [DataMember]
        public bool CanViewActions { get; set; }

        /// <summary>
        /// Gets or sets a value indicating whether this instance has pending configuration items operation.
        /// </summary>
        /// <value>
        ///   <c>true</c> if this instance has pending configuration items operation; otherwise, <c>false</c>.
        /// </value>
        [DataMember]
        public bool HasPendingConfigItemsOperation { get; set; }

        /// <summary>
        /// Gets or sets a value indicating whether this instance has pending firmware upgrade operation.
        /// </summary>
        /// <value>
        ///   <c>true</c> if this instance has pending firmware upgrade operation; otherwise, <c>false</c>.
        /// </value>
        [DataMember]
        public bool HasPendingFirmwareUpgradeOperation { get; set; }

        /// <summary>
        /// Gets or sets a value indicating whether this instance has pending report a problem application operation.
        /// </summary>
        /// <value>
        ///   <c>true</c> if this instance has pending report a problem application operation; otherwise, <c>false</c>.
        /// </value>
        [DataMember]
        public bool HasPendingReportAProblemAppOperation { get; set; }

        /// <summary>
        /// Gets or sets a value indicating whether this instance has pending clone to policy operation.
        /// </summary>
        /// <value>
        ///   <c>true</c> if this instance has pending clone to policy operation; otherwise, <c>false</c>.
        /// </value>
        [DataMember]
        public bool HasPendingCloneToPolicyOperation { get; set; }

        /// <summary>
        /// Gets or sets a value indicating whether this instance has pending assess and remediate operation.
        /// </summary>
        /// <value>
        ///   <c>true</c> if this instance has pending assess and remediate operation; otherwise, <c>false</c>.
        /// </value>
        [DataMember]
        public bool HasPendingAssessAndRemediateOperation { get; set; }

        /// <summary>
        /// Gets or sets the last configuration items.
        /// </summary>
        /// <value>
        /// The last configuration items.
        /// </value>
        [DataMember]
        public IEnumerable<SdsConfigItemDto> LastConfigItems { get; set; }

        /// <summary>
        /// Gets or sets the operation firmware update at UTC.
        /// </summary>
        /// <value>
        /// The operation firmware update at UTC.
        /// </value>
        [DataMember]
        public DateTime? OperationFirmwareUpdateAtUtc { get; set; }

        /// <summary>
        /// Gets or sets the operation firmware update version.
        /// </summary>
        /// <value>
        /// The operation firmware update version.
        /// </value>
        [DataMember]
        public string OperationFirmwareUpdateVersion { get; set; }

        /// <summary>
        /// Gets or sets the operation reboot at UTC.
        /// </summary>
        /// <value>
        /// The operation reboot at UTC.
        /// </value>
        [DataMember]
        public DateTime? OperationRebootAtUtc { get; set; }

        /// <summary>
        /// Gets or sets the jam credential.
        /// </summary>
        /// <value>
        /// The jam credential.
        /// </value>
        [DataMember]
        public SdsCredentialDto SdsCredential { get; set; }

        /// <summary>
        /// Gets or sets the relevant eventlogs.
        /// </summary>
        /// <value>
        /// The relevant eventlogs.
        /// </value>
        [DataMember]
        public IEnumerable<SdsDeviceEventDto> RelevantEventlogs { get; set; }

        /// <summary>
        /// Gets or sets the actions.
        /// </summary>
        /// <value>
        /// The actions.
        /// </value>
        [DataMember]
        public IEnumerable<SdsDeviceActionDto> Actions { get; set; }

        /// <summary>
        /// Gets or sets the has report a problem application.
        /// </summary>
        /// <value>
        /// The has report a problem application.
        /// </value>
        [DataMember]
        public bool HasReportAProblemApp { get; set; }

        /// <summary>
        /// Gets or sets a value indicating whether this device can install Report a Problem App
        /// </summary>
        /// <value>
        ///   <c>true</c> if this instance can install report a problem application; otherwise, <c>false</c>.
        /// </value>
        [DataMember]
        public bool CanInstallReportAProblemApp { get; set; }

        /// <summary>
        /// Gets or sets the last assess and remediate operation.
        /// </summary>
        /// <value>
        /// The last assess and remediate operation.
        /// </value>
        [DataMember]
        public SdsDeviceOperationDto LastAssessAndRemediateOperation { get; set; }
    }


    /// <summary>
    /// 
    /// </summary>
    [DataContract]
    public class SdsDeviceFirmwareImageInfoDto : EntityDto
    {
        /// <summary>
        /// Gets or sets the updated at UTC.
        /// </summary>
        /// <value>
        /// The updated at UTC.
        /// </value>
        [DataMember]
        public DateTime UpdatedAtUtc { get; set; }

        /// <summary>
        /// Gets or sets the title.
        /// </summary>
        /// <value>
        /// The title.
        /// </value>
        [DataMember]
        public string Title { get; set; }

        /// <summary>
        /// Gets or sets the description.
        /// </summary>
        /// <value>
        /// The description.
        /// </value>
        [DataMember]
        public string Description { get; set; }

        /// <summary>
        /// Gets or sets the build version.
        /// </summary>
        /// <value>
        /// The build version.
        /// </value>
        [DataMember]
        public string BuildVersion { get; set; }

        /// <summary>
        /// Gets or sets the date code.
        /// </summary>
        /// <value>
        /// The date code.
        /// </value>
        [DataMember]
        public string DateCode { get; set; }

        /// <summary>
        /// Gets or sets the date UTC.
        /// </summary>
        /// <value>
        /// The date UTC.
        /// </value>
        [DataMember]
        public DateTime? DateUtc { get; set; }

        /// <summary>
        /// Gets or sets the release version.
        /// </summary>
        /// <value>
        /// The release version.
        /// </value>
        [DataMember]
        public string ReleaseVersion { get; set; }
    }


    /// <summary>
    /// 
    /// </summary>
    [DataContract]
    public class SdsDeviceSupplyDto : EntityDto
    {
        /// <summary>
        /// Gets or sets the consumable.
        /// </summary>
        /// <value>
        /// The consumable.
        /// </value>
        [DataMember]
        public string Consumable { get; set; }

        /// <summary>
        /// Gets or sets the occurrence.
        /// </summary>
        /// <value>
        /// The occurrence.
        /// </value>
        [DataMember]
        public string Occurrence { get; set; }

        /// <summary>
        /// Gets or sets the status.
        /// </summary>
        /// <value>
        /// The status.
        /// </value>
        [DataMember]
        public string Status { get; set; }

        /// <summary>
        /// Gets or sets the model number.
        /// </summary>
        /// <value>
        /// The model number.
        /// </value>
        [DataMember]
        public string ModelNumber { get; set; }

        /// <summary>
        /// Gets or sets the last collection time UTC.
        /// </summary>
        /// <value>
        /// The last collection time UTC.
        /// </value>
        [DataMember]
        public DateTime? LastCollectionTimeUtc { get; set; }

        /// <summary>
        /// Gets or sets the level value percent.
        /// </summary>
        /// <value>
        /// The level value percent.
        /// </value>
        [DataMember]
        public int? LevelValuePercent { get; set; }

        /// <summary>
        /// Gets or sets the serial number.
        /// </summary>
        /// <value>
        /// The serial number.
        /// </value>
        [DataMember]
        public string SerialNumber { get; set; }

        /// <summary>
        /// Gets or sets the manufacture date UTC.
        /// </summary>
        /// <value>
        /// The manufacture date UTC.
        /// </value>
        [DataMember]
        public DateTime? ManufactureDateUtc { get; set; }

        /// <summary>
        /// Gets or sets the install date UTC.
        /// </summary>
        /// <value>
        /// The install date UTC.
        /// </value>
        [DataMember]
        public DateTime? InstallDateUtc { get; set; }

        /// <summary>
        /// Gets or sets the ocv status.
        /// </summary>
        /// <value>
        /// The ocv status.
        /// </value>
        [DataMember]
        public string OcvStatus { get; set; }

        /// <summary>
        /// Gets or sets the replacement date UTC.
        /// </summary>
        /// <value>
        /// The replacement date UTC.
        /// </value>
        [DataMember]
        public DateTime? ReplacementDateUtc { get; set; }

        /// <summary>
        /// Gets or sets a value indicating whether [changed too early].
        /// </summary>
        /// <value>
        ///   <c>true</c> if [changed too early]; otherwise, <c>false</c>.
        /// </value>
        [DataMember]
        public bool ChangedTooEarly { get; set; }
    }

    /// <summary>
    /// ConfigItemEntry
    /// </summary>
    [DataContract]
    public class SdsConfigItemDto
    {
        private readonly List<string> WriteOnlyConfigItems = new List<string>
                                                                 {
                                                                     "FileSystemSettings",
                                                                     "MoreAppsInstallation",
                                                                     "MoreAppsUninstallation"
                                                                 };

        private readonly List<string> ReadOnlyConfigItems = new List<string>
                                                                {
                                                                    "CountDetails",
                                                                    "CountTotals",
                                                                    "Coverage",
                                                                    "FileSystemAccessNfsEnabled",
                                                                    "FileSystemAccessPjlEnabled",
                                                                    "FileSystemAccessPmlEnabled",
                                                                    "FileSystemAccessPsEnabled",
                                                                    "WsPrintEnabled",
                                                                    "Supplies",
                                                                };

        /// <summary>
        /// Gets or sets the identifier.
        /// </summary>
        /// <value>
        /// The identifier.
        /// </value>
        public string Id { get; set; }

        /// <summary>
        /// Gets or sets the name.
        /// </summary>
        /// <value>
        /// The name.
        /// </value>
        [DataMember]
        public JamConfigItemEnum Name { get; set; }

        /// <summary>
        /// Gets or sets the set value.
        /// </summary>
        /// <value>
        /// The set value.
        /// </value>
        [DataMember]
        public string SetValue { get; set; }

        /// <summary>
        /// Gets a value indicating whether this instance can read.
        /// </summary>
        /// <value>
        ///   <c>true</c> if this instance can read; otherwise, <c>false</c>.
        /// </value>
        [DataMember]
        public bool CanRead
        {
            get
            {
                return !this.WriteOnlyConfigItems.Contains(this.Name.ToString());
            }
        }

        /// <summary>
        /// Gets a value indicating whether this instance can write.
        /// </summary>
        /// <value>
        ///   <c>true</c> if this instance can write; otherwise, <c>false</c>.
        /// </value>
        [DataMember]
        public bool CanWrite
        {
            get
            {
                return !this.ReadOnlyConfigItems.Contains(this.Name.ToString());
            }
        }

        /// <summary>
        /// Gets or sets the created at UTC.
        /// </summary>
        /// <value>
        /// The created at UTC.
        /// </value>
        [DataMember]
        public DateTime? CreatedAtUtc { get; set; }

        /// <summary>
        /// Gets or sets the result.
        /// </summary>
        /// <value>
        /// The result.
        /// </value>
        [DataMember]
        public string Result { get; set; }

        /// <summary>
        /// Gets or sets the reason.
        /// </summary>
        /// <value>
        /// The reason.
        /// </value>
        [DataMember]
        public string Reason { get; set; }

        /// <summary>
        /// Gets or sets the value.
        /// </summary>
        /// <value>
        /// The value.
        /// </value>
        [DataMember]
        public string Value { get; set; }

        /// <summary>
        /// Gets or sets the single value.
        /// </summary>
        /// <value>
        /// The single value.
        /// </value>
        [DataMember]
        public string SingleValue { get; set; }
    }

    /// <summary>
    /// 
    /// </summary>
    /// <seealso cref="EntityDto" />
    [DataContract]
    public class SdsDeviceEventDto : EntityDto
    {
        [DataMember]
        public InstalledProductSdsEventTypeEnum? EventType { get; set; }
        [DataMember]
        public string EventCode { get; set; }
        [DataMember]
        public DateTime EventDateUtc { get; set; }

        /// <summary>
        /// Required, integer, an identifier to differentiate different occurrences of the same event
        /// </summary>
        [DataMember]
        public int SequenceNumber { get; set; }

        /// <summary>
        /// Required, integer, the number of these events that have occurred without another event between them.
        /// </summary>
        [DataMember]
        public int EventOccurrences { get; set; }

        /// <summary>
        /// Required, long unsigned integer, the number of impressions on the device at the time of the error.
        /// </summary>
        [DataMember]
        public long TotalImpressions { get; set; }

        /// <summary>
        /// Required, string, the version of firmware on the device at the time of the error.
        /// </summary>
        [DataMember]
        public string FirmwareVersion { get; set; }

        /// <summary>
        /// Optional, Localized short description of the event.
        /// </summary>
        [DataMember]
        public string EventDescription { get; set; }

        /// <summary>
        /// Optional, URI,  fully qualified path to the documentation for this error.
        /// </summary>
        [DataMember]
        public string Link { get; set; }

        /// <summary>
        /// Flag set by Dealer that ignore this event
        /// </summary>
        [DataMember]
        public bool IsHidden { get; set; }

        /// <summary>
        /// Gets or sets the customer identifier.
        /// </summary>
        /// <value>
        /// The customer identifier.
        /// </value>
        [DataMember]
        public string CustomerId { get; set; }

        /// <summary>
        /// Gets or sets the dealer identifier.
        /// </summary>
        /// <value>
        /// The dealer identifier.
        /// </value>
        [DataMember]
        public string DealerId { get; set; }

        /// <summary>
        /// Gets or sets the device identifier.
        /// </summary>
        /// <value>
        /// The device identifier.
        /// </value>
        [DataMember]
        public string DeviceId { get; set; }
    }

    [DataContract]
    public class SdsDeviceActionDto : EntityDto
    {
        // ProductNumber -> è diverso dal serial number

        /// <summary>
        /// Gets or sets the customer identifier.
        /// </summary>
        /// <value>
        /// The customer identifier.
        /// </value>
        [DataMember]
        public string CustomerId { get; set; }

        /// <summary>
        /// Gets or sets the customer code.
        /// </summary>
        /// <value>
        /// The customer code.
        /// </value>
        [DataMember]
        public string CustomerCode { get; set; }

        /// <summary>
        /// Gets or sets the customer description.
        /// </summary>
        /// <value>
        /// The customer description.
        /// </value>
        [DataMember]
        public string CustomerDescription { get; set; }

        /// <summary>
        /// Gets or sets the dealer identifier.
        /// </summary>
        /// <value>
        /// The dealer identifier.
        /// </value>
        [DataMember]
        public string DealerId { get; set; }

        /// <summary>
        /// Gets or sets the dealer code.
        /// </summary>
        /// <value>
        /// The dealer code.
        /// </value>
        [DataMember]
        public string DealerCode { get; set; }

        /// <summary>
        /// Gets or sets the dealer description.
        /// </summary>
        /// <value>
        /// The dealer description.
        /// </value>
        [DataMember]
        public string DealerDescription { get; set; }

        /////// <summary>
        /////// Gets or sets the installed product last update.
        /////// </summary>
        /////// <value>
        /////// The installed product last update.
        /////// </value>
        ////[DataMember]
        ////[DisplayFormat(DataFormatString = "{0:s}")]
        ////[ExcelColumn(UseDisplayFormatString = true)]
        ////public DateTime InstalledProductLastUpdate { get; set; }

        [DataMember]
        public string InstalledProductSerialNumber { get; set; }

        /// <summary>
        /// Gets or sets the brand.
        /// </summary>
        /// <value>
        /// The brand.
        /// </value>
        [DataMember]
        public string Brand { get; set; }

        /// <summary>
        /// Gets or sets the model.
        /// </summary>
        /// <value>
        /// The model.
        /// </value>
        [DataMember]
        public string Model { get; set; }


        /// <summary>
        /// Required, guid, the actionJamid of the action
        /// </summary>
        [DataMember]
        public Guid ActionJamId { get; set; }

        /// <summary>
        /// Required, string, the id of the device
        /// </summary>
        [DataMember]
        public string DeviceId { get; set; }

        /// <summary>
        /// Required, string, the code associated with the action.
        /// </summary>
        [DataMember]
        public string Code { get; set; }

        /// <summary>
        /// Optional, string, the event code associated with this action if available
        /// </summary>
        [DataMember]
        public string EventCodeContext { get; set; }

        /// <summary>
        /// Date: Required, date time in UTC, the date and time the action was created.
        /// </summary>
        [DataMember]
        [DisplayFormat(DataFormatString = "{0:s}")]
        public DateTime ActionDateUtc { get; set; }

        /// <summary>
        /// Gets or sets the severity.
        /// </summary>
        /// <value>
        /// The severity.
        /// </value>
        [DataMember]
        public JamActionServerityEnum Severity { get; set; }

        /// <summary>
        /// Gets or sets the state of the current.
        /// </summary>
        /// <value>
        /// The state of the current.
        /// </value>
        [DataMember]
        public SdsActionUpdateStateEnum CurrentState { get; set; }

        /// <summary>
        /// Gets or sets the status reports.
        /// </summary>
        /// <value>
        /// The status reports.
        /// </value>
        [DataMember]
        public IEnumerable<SdsActionStatusReportDto> StatusReports { get; set; }

        /// <summary>
        /// Optional, string, localized title of the action document.
        /// </summary>
        [DataMember]
        public string Title { get; set; }

        /// <summary>
        /// Optional, Localized string for repair time
        /// </summary>
        [DataMember]
        public string MeanTimeToRepair { get; set; }

        /// <summary>
        /// Optional, localized description of expertise required
        /// </summary>
        [DataMember]
        public string ServiceLevel { get; set; }

        /// <summary>
        /// Optional, localized array of strings that describe tools required.
        /// </summary>
        [DataMember]
        public string Tools { get; set; }

        /// <summary>
        /// Optional, list of updated replacement part numbers.
        /// </summary>
        [DataMember]
        public string Parts { get; set; }

        /// <summary>
        /// Optional, URI, fully qualified path to the localized documentation for this action.Link expires after 7 days.A new link will be created for each call.
        /// </summary>
        [DataMember]
        public string Link { get; set; }

        /// <summary>
        /// Optional, long unsigned integer, the number of impressions on the device at the time of the action creation.
        /// </summary>
        [DataMember]
        public long? TotalImpressions { get; set; }

        /// <summary>
        /// Optional, string, the version of firmware on the device at the time of the action creation.
        /// </summary>
        [DataMember]
        public string FirmwareVersion { get; set; }

        /// <summary>
        /// Gets or sets the type of the action.
        /// </summary>
        [DataMember]
        public string ActionType { get; set; }

        /// <summary>
        /// Gets or sets the predictive data.
        /// </summary>
        [DataMember]
        public SdsActionPredictiveDataDto PredictiveData { get; set; }

        /// <summary>
        /// Gets or sets the customer reported problem data.
        /// </summary>
        [DataMember]
        public SdsActionCustomerReportedProblemDataDto CustomerReportedProblemData { get; set; }
    }

    /// <summary>
    /// SdsActionPredictiveDataDto
    /// </summary>
    [DataContract]
    public class SdsActionPredictiveDataDto
    {
        /// <summary>
        /// Gets or sets the probability.
        /// </summary>
        [DataMember]
        public float Probability { get; set; }

        /// <summary>
        /// Gets or sets the day window.
        /// </summary>
        [DataMember]
        public int DayWindow { get; set; }
    }

    /// <summary>
    /// SdsActionCustomerReportedProblemDataDto
    /// </summary>
    [DataContract]
    public class SdsActionCustomerReportedProblemDataDto
    {
        /// <summary>
        /// Gets or sets the category.
        /// </summary>
        [DataMember]
        public string Category { get; set; }

        /// <summary>
        /// Gets or sets the subcategory.
        /// </summary>
        [DataMember]
        public string Subcategory { get; set; }

        /// <summary>
        /// Gets or sets the details.
        /// </summary>
        [DataMember]
        public string Details { get; set; }
    }

    /// <summary>
    /// SdsActionStatusReport
    /// </summary>
    /// <seealso cref="EntityDto" />
    [DataContract]
    public class SdsActionStatusReportDto : EntityDto
    {
        /// <summary>
        /// Date: Required, date time in UTC, the date and time the action was updated
        /// </summary>
        [DataMember]
        public DateTime ActionDateUtc { get; set; }

        /// <summary>
        /// Optional, see Action State for definition, new state of the action.If not provided then Severity must be provided
        /// </summary>
        [DataMember]
        public SdsActionUpdateStateEnum? State { get; set; }

        /// <summary>
        /// Optional, see Action Severity for definition, new state of the action.If not provided then State must be provided
        /// </summary>
        [DataMember]
        public JamActionServerityEnum? Severity { get; set; }

        /// <summary>
        /// Optional, string, any additional comments related to the issue.
        /// </summary>
        [DataMember]
        public string Comments { get; set; }
    }

    /// <summary>
    /// 
    /// </summary>
    [DataContract]
    public class SdsCredentialDto : EntityDto
    {
        /// <summary>
        /// Gets or sets the updated at UTC.
        /// </summary>
        /// <value>
        /// The updated at UTC.
        /// </value>
        [DataMember]
        public DateTime UpdatedAtUtc { get; set; }

        /// <summary>
        /// Gets or sets a value indicating whether this instance is admin set.
        /// </summary>
        /// <value>
        ///   <c>true</c> if this instance is admin set; otherwise, <c>false</c>.
        /// </value>
        [DataMember]
        public bool IsAdminSet { get; set; }

        /// <summary>
        /// Gets or sets a value indicating whether this instance is SNMP v1 v2 read.
        /// </summary>
        /// <value>
        ///   <c>true</c> if this instance is SNMP v1 v2 read; otherwise, <c>false</c>.
        /// </value>
        [DataMember]
        public bool IsSnmpV1V2Read { get; set; }

        /// <summary>
        /// Gets or sets a value indicating whether this instance is SNMP v1 v2 read write.
        /// </summary>
        /// <value>
        ///   <c>true</c> if this instance is SNMP v1 v2 read write; otherwise, <c>false</c>.
        /// </value>
        [DataMember]
        public bool IsSnmpV1V2ReadWrite { get; set; }

        /// <summary>
        /// Gets or sets a value indicating whether this instance is SNMP v3.
        /// </summary>
        /// <value>
        ///   <c>true</c> if this instance is SNMP v3; otherwise, <c>false</c>.
        /// </value>
        [DataMember]
        public bool IsSnmpV3 { get; set; }
    }

    /// <summary>
    /// 
    /// </summary>
    [DataContract]
    public class SdsDeviceOperationDto : EntityDto
    {
        /// <summary>
        /// Gets or sets the operation identifier.
        /// </summary>
        /// <value>
        /// The operation identifier.
        /// </value>
        [DataMember]
        public int OperationId { get; set; }

        /// <summary>
        /// Gets or sets the created time UTC.
        /// </summary>
        /// <value>
        /// The created time UTC.
        /// </value>
        [DataMember]
        public DateTime CreatedTimeUtc { get; set; }

        /// <summary>
        /// Gets or sets the last updated time UTC.
        /// </summary>
        /// <value>
        /// The last updated time UTC.
        /// </value>
        [DataMember]
        public DateTime LastUpdatedTimeUtc { get; set; }

        /// <summary>
        /// Gets or sets the operation.
        /// </summary>
        /// <value>
        /// The operation.
        /// </value>
        [DataMember]
        public InstalledProductJamOperationTypeEnum Operation { get; set; }

        /// <summary>
        /// Gets or sets the user account identifier.
        /// </summary>
        /// <value>
        /// The user account identifier.
        /// </value>
        [DataMember]
        public int UserAccountId { get; set; }

        /// <summary>
        /// Gets or sets the result.
        /// </summary>
        /// <value>
        /// The result.
        /// </value>
        [DataMember]
        public JamOperationResultEnum Result { get; set; }

        /// <summary>
        /// Gets or sets the details.
        /// </summary>
        /// <value>
        /// The details.
        /// </value>
        [DataMember]
        public string Details { get; set; }

        /// <summary>
        /// Gets or sets the operation information.
        /// </summary>
        /// <value>
        /// The operation information.
        /// </value>
        [DataMember]
        public string OperationInfo { get; set; }

        /// <summary>
        /// Gets or sets a value indicating whether this instance is pending.
        /// </summary>
        /// <value>
        ///   <c>true</c> if this instance is pending; otherwise, <c>false</c>.
        /// </value>
        [DataMember]
        public bool IsPending { get; set; }

        /// <summary>
        /// Gets or sets a value indicating whether this instance is success.
        /// </summary>
        /// <value>
        ///   <c>true</c> if this instance is success; otherwise, <c>false</c>.
        /// </value>
        [DataMember]
        public bool IsSuccess { get; set; }

        /// <summary>
        /// Gets or sets a value indicating whether this instance is error.
        /// </summary>
        /// <value>
        ///   <c>true</c> if this instance is error; otherwise, <c>false</c>.
        /// </value>
        [DataMember]
        public bool IsError { get; set; }

        /// <summary>
        /// Gets or sets a value indicating whether this instance is credentials operation.
        /// </summary>
        /// <value>
        ///   <c>true</c> if this instance is credentials operation; otherwise, <c>false</c>.
        /// </value>
        [DataMember]
        public bool IsCredentialsOperation { get; set; }
    }
}
