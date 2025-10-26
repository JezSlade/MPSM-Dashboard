using MpsMonitor.Sdk.Models.Enums;
using System;
using System.Collections.Generic;
using System.Runtime.Serialization;
using System.Text;

namespace MpsMonitor.Sdk.Models.Dto
{
    class ExplorerDto
    {
    }
    /// <inheritdoc />
    /// <summary>
    /// Explorer Data Dto
    /// </summary>
    [DataContract]
    public class ExplorerDataDto : EntityDto
    {
        /// <summary>
        /// Constructor with 0 argumnet for automapper
        /// </summary>
        public ExplorerDataDto()
        {
            ExplorerDataInfos = new List<ExplorerDataInfoDto>();
            Configurations = new List<ExplorerConfigurationBaseDto>();
            ClusteredSlaves = new List<ExplorerDataDto>();
        }

        /// <summary>
        /// CreatedAt
        /// </summary>
        [DataMember]
        public DateTime CreatedAt { get; set; }

        /// <summary>
        /// Identifier
        /// </summary>
        [DataMember]
        public string Identifier { get; set; }

        /// <summary>
        /// IP Address
        /// </summary>
        [DataMember]
        public string IP { get; set; }

        /// <summary>
        /// System Name
        /// </summary>
        [DataMember]
        public string SystemName { get; set; }

        /// <summary>
        /// Customer id
        /// </summary>
        [DataMember]
        public string DealerId { get; set; }

        /// <summary>
        /// Customer Code
        /// </summary>
        [DataMember]
        public string DealerCode { get; set; }

        /// <summary>
        /// Customer Description
        /// </summary>
        [DataMember]
        public string DealerDescription { get; set; }

        /// <summary>
        /// Customer id
        /// </summary>
        [DataMember]
        public string CustomerId { get; set; }

        /// <summary>
        /// Customer Code
        /// </summary>
        [DataMember]
        public string CustomerCode { get; set; }

        /// <summary>
        /// Customer Description
        /// </summary>
        [DataMember]
        public string CustomerDescription { get; set; }

        /// <summary>
        /// Automatic Update
        /// </summary>
        [DataMember]
        public bool AutomaticUpdate { get; set; }

        /// <summary>
        /// Build Number
        /// </summary>
        [DataMember]
        public string BuildNumber { get; set; }

        /// <summary>
        /// Build Date
        /// </summary>
        [DataMember]
        public DateTime? BuildDate { get; set; }

        /// <summary>
        /// True if the connector is embedded on the printer
        /// </summary>
        [DataMember]
        public bool IsEmbedded { get; set; }

        /// <summary>
        /// OID Table Version
        /// </summary>
        [DataMember]
        public int? TableVersion { get; set; }

        /// <summary>
        /// Service Build Number
        /// </summary>
        [DataMember]
        public string ServiceBuildNumber { get; set; }

        /// <summary>
        /// ServiceMajor
        /// </summary>
        [DataMember]
        public int ServiceMajor
        {
            get
            {
                if (string.IsNullOrEmpty(ServiceBuildNumber))
                {
                    return 0;
                }

                Version v = new Version(ServiceBuildNumber);
                return v.Major;
            }
        }

        /// <summary>
        /// ConfiguratorBuildNumber
        /// </summary>
        [DataMember]
        public string ConfiguratorBuildNumber { get; set; }

        /// <summary>
        /// PollingInterval
        /// </summary>
        [DataMember]
        public int PollingInterval { get; set; }

        /// <summary>
        /// LastUpload
        /// </summary>
        [DataMember]
        public DateTime? LastUpload { get; set; }

        /// <summary>
        /// Version
        /// </summary>
        [DataMember]
        public string Version { get; set; }

        /// <summary>
        /// Platform in case of embedded connector
        /// </summary>
        [DataMember]
        public string Platform { get; set; }

        /// <summary>
        /// IsUpdatable
        /// </summary>
        public bool IsUpdatable { get; set; }

        /// <summary>
        /// LastPing
        /// </summary>
        [DataMember]
        public DateTime? LastPing { get; set; }

        /// <summary>
        /// HasWarning
        /// </summary>
        [DataMember(IsRequired = true)]
        public bool HasWarning { get; set; }

        /// <summary>
        /// PingIsOutOfDate
        /// </summary>
        [DataMember(IsRequired = true)]
        public bool PingIsOutOfDate { get; set; }

        /// <summary>
        /// DataIsOutOfDate
        /// </summary>
        [DataMember(IsRequired = true)]
        public bool DataIsOutOfDate { get; set; }

        /// <summary>
        /// NeverReceiveData
        /// </summary>
        [DataMember(IsRequired = true)]
        public bool NeverReceiveData { get; set; }

        /// <summary>
        /// NoValidConfiguration
        /// </summary>
        [DataMember(IsRequired = true)]
        public bool NoValidConfiguration { get; set; }

        /// <summary>
        /// LastRun
        /// </summary>
        [DataMember]
        public DateTime? LastRun { get; set; }

        /// <summary>
        /// LastNetworkDiscovery
        /// </summary>
        [DataMember]
        public DateTime? LastNetworkDiscovery { get; set; }

        /// <summary>
        /// TimeZone
        /// </summary>
        [DataMember]
        public string TimeZone { get; set; }

        /// <summary>
        /// TimeZone
        /// </summary>
        [DataMember]
        public string TimeZoneIana { get; set; }

        // ExplorerDataJam
        [DataMember]
        public string ExplorerDataJamExplorerJamVersion { get; set; }
        [DataMember]
        public string ExplorerDataJamVersion { get; set; }
        [DataMember]
        public ExplorerDataJamStatusEnum? ExplorerDataJamConnectorStatus { get; set; }
        [DataMember]
        public DateTime? ExplorerDataJamLastContactTimeUtc { get; set; }
        [DataMember]
        public string ExplorerDataJamRegistrationKey { get; set; }
        [DataMember]
        public DateTime? ExplorerDataJamLastUploadUtc { get; set; }
        [DataMember]
        public string ExplorerDataJamInstalledComputer { get; set; }
        [DataMember]
        public string ExplorerDataJamWebProxyAddress { get; set; }
        [DataMember]
        public int ExplorerDataJamWebProxyPort { get; set; }
        [DataMember]
        public int? ExplorerDataJamConnectorId { get; set; }

        /// <summary>
        /// The cluster to which this eXplorer belongs to, if any
        /// </summary>
        [DataMember]
        public ExplorerClusterDto ExplorerCluster { get; set; }

        /// <summary>
        /// True if this eXplorer is the master in the cluster,
        /// that is its configurations become the cluster's configurations
        /// </summary>
        [DataMember]
        public bool IsMasterInCluster { get; set; }

        /// <summary>
        /// The list of ExplorerDataInfos
        /// </summary>
        [DataMember]
        public IEnumerable<ExplorerDataInfoDto> ExplorerDataInfos { get; set; }

        /// <summary>
        /// The list of Configurations
        /// </summary>
        [DataMember]
        public IEnumerable<ExplorerConfigurationBaseDto> Configurations { get; set; }

        /// <summary>
        /// The list of clustered slaves, if any
        /// </summary>
        [DataMember]
        public IEnumerable<ExplorerDataDto> ClusteredSlaves { get; set; }

        [DataMember]
        public bool IsSelected { get; set; }

        [DataMember]
        public bool LogIsReady { get; set; }

        [DataMember]
        public bool SendLog { get; set; }

    }

    /// <summary>
    /// The info related to the system where this eXplorer is installed on
    /// </summary>
    [DataContract]
    public class ExplorerDataInfoDto : EntityDto
    {
        /// <summary>
        /// The key info
        /// </summary>
        [DataMember]
        public string Key { get; set; }
        /// <summary>
        /// The value of the info
        /// </summary>
        [DataMember]
        public string Value { get; set; }
        /// <summary>
        /// Date time of last update
        /// </summary>
        [DataMember]
        public DateTime LastUpdate { get; set; }
    }

    /// <summary>
    /// Explorer Configuration Dto
    /// </summary>
    [DataContract]
    public class ExplorerConfigurationBaseDto : EntityDto
    {
        /// <summary>
        /// Description
        /// </summary>
        [DataMember]
        public virtual string Description { get; set; }

        /// <summary>
        /// Explorer Data
        /// </summary>
        [DataMember]
        public string ExplorerDataSystemName { get; set; }

        /// <summary>
        /// IsValidConfiguration
        /// </summary>
        [DataMember]
        public bool IsValidConfiguration { get; set; }

        /// <summary>
        /// IsEnable
        /// </summary>
        [DataMember]
        public bool IsEnable { get; set; }

        /// <summary>
        /// UseAutoAssign
        /// </summary>
        [DataMember]
        public bool UseAutoAssign { get; set; }

        /// <summary>
        /// Explorer Data
        /// </summary>
        [DataMember]
        public string ExplorerDataId { get; set; }

        /// <summary>
        /// Explorer Data
        /// </summary>
        [DataMember]
        public string CustomerId { get; set; }
    }

    /// <summary>
    /// Represents a group of ExplorerDatas within which the AutoFix may run
    /// </summary>
    [DataContract]
    public class ExplorerClusterDto : EntityDto
    {
        /// <summary>
        /// Constructor with 0 argumnet for automapper
        /// </summary>
        public ExplorerClusterDto()
        {
            ExplorerDatas = new List<ExplorerDataDto>();
            Subnets = new List<string>();
        }

        /// <summary>
        /// The customer
        /// </summary>
        [DataMember]
        public CustomerBaseDto Customer { get; set; }

        /// <summary>
        /// Cluster name
        /// </summary>
        [DataMember]
        public string Description { get; set; }

        /// <summary>
        /// The number of days after which a non communicating eXplorer will be switched off
        /// and a communicating eXplorer switched on in the same cluster
        /// </summary>
        [DataMember]
        public int AutoFixDayLimit { get; set; }

        /// <summary>
        /// The list of eXplorers inside the cluster
        /// </summary>
        [DataMember]
        public IList<ExplorerDataDto> ExplorerDatas { get; set; }

        /// <summary>
        /// Subnets in the cluster
        /// </summary>
        [DataMember]
        public IList<string> Subnets { get; set; }
    }


    [DataContract]
    public class ExplorerConfigurationDto : ExplorerConfigurationBaseDto
    {
        /// <summary>
        /// ExplorerSchedules
        /// </summary>
        [DataMember]
        public IList<ExplorerScheduleDto> ExplorerSchedules { get; set; }

        /// <summary>
        /// ExplorerSubnets
        /// </summary>
        [DataMember]
        public IList<ExplorerSubnetDto> ExplorerSubnets { get; set; }

        /// <summary>
        /// This property in not avalaible
        /// </summary>
        [DataMember]
        public string IdTicket { get; set; }


        /// <summary>
        /// MaxProcess
        /// </summary>
        [DataMember]
        public int MaxProcess { get; set; }

        /// <summary>
        /// MaxThread
        /// </summary>
        [DataMember]
        public int MaxThread { get; set; }

        /// <summary>
        /// ActivateExclusions
        /// </summary>
        [DataMember]
        public bool ActivateExclusions { get; set; }

        /// <summary>
        /// This property in not avalaible
        /// </summary>
        [DataMember]
        public bool UseEmbeddedOIDMap { get; set; }

        /// <summary>
        /// This property in not avalaible
        /// </summary>
        [DataMember]
        public string MpsUrl { get; set; }

        /// <summary>
        /// This property in not avalaible
        /// </summary>
        [DataMember]
        public string DeviceDetectionOidArray { get; set; }

        /// <summary>
        /// ActivateOverrides
        /// </summary>
        [DataMember]
        public bool ActivateOverrides { get; set; }

        /// <summary>
        /// GenerateXMLData
        /// </summary>
        [DataMember]
        public bool GenerateXMLData { get; set; }

        /// <summary>
        /// DisableWalks
        /// </summary>
        [DataMember]
        public bool DisableWalks { get; set; }

        /// <summary>
        /// ScanPc
        /// </summary>
        [DataMember]
        public bool ScanPc { get; set; }

        /// <summary>
        /// This property in not avalaible
        /// </summary>
        [DataMember]
        public string VersionTest { get; set; }

        /// <summary>
        /// UseHPSecureCounters
        /// </summary>
        [DataMember]
        public bool UseHPSecureCounters { get; set; }

        /// <summary>
        /// Community
        /// </summary>
        [DataMember]
        public string Community { get; set; }

        /// <summary>
        /// ScanTimeout
        /// </summary>
        [DataMember]
        public int? ScanTimeout { get; set; }

        /// <summary>
        /// WalkTimeout
        /// </summary>
        [DataMember]
        public int? WalkTimeout { get; set; }

        /// <summary>
        /// GetTimeout
        /// </summary>
        [DataMember]
        public int? GetTimeout { get; set; }

        // TODO: Non la vedo nella vista dove è usata
        // public string CustomTests { get; set; }

        /// <summary>
        /// This property in not avalaible
        /// </summary>
        [DataMember]
        public bool SendEnvironmentInfo { get; set; }

        /// <summary>
        /// This property in not avalaible
        /// </summary>
        [DataMember]
        public string ExplorerJamcParameters { get; set; }

        /// <summary>
        /// WinceTimeoutSocket
        /// </summary>
        [DataMember]
        public int WinceTimeoutSocket { get; set; }

        /// <summary>
        /// WinceDeepSleepDisable
        /// </summary>
        [DataMember]
        public bool WinceDeepSleepDisable { get; set; }

        /// <summary>
        /// UseSNMPv2Version
        /// </summary>
        [DataMember]
        public bool UseSNMPv2Version { get; set; }

        /// <summary>
        /// UseSNMPv2Version
        /// </summary>
        [DataMember]
        public bool UseHpProxy { get; set; }

    }


    /// <summary>
    /// ExplorerScheduleDto
    /// </summary>
    [DataContract]
    public class ExplorerScheduleDto : EntityDto
    {
        /// <summary>
        /// CustomerId
        /// </summary>
        [DataMember]
        public string CustomerId { get; set; }

        /// <summary>
        /// ExplorerConfigurationId
        /// </summary>
        [DataMember]
        public string ExplorerConfigurationId { get; set; }

        /// <summary>
        /// Occurence
        /// </summary>
        [DataMember]
        public string Occurence { get; set; }

        /// <summary>
        /// StartAt
        /// </summary>
        [DataMember]
        public DateTime StartAt { get; set; }

        /// <summary>
        /// Days
        /// </summary>
        [DataMember]
        public string Days { get; set; }

        /// <summary>
        /// TimeZone
        /// </summary>
        [DataMember]
        public string TimeZone { get; set; }

        /// <summary>
        /// LastRequest
        /// </summary>
        [DataMember]
        public DateTime? LastRequest { get; set; }
    }

    /// <summary>
    /// ExplorerSubnetDto
    /// </summary>
    [DataContract]
    public class ExplorerSubnetDto : EntityDto
    {
        /// <summary>
        /// CustomerId
        /// </summary>
        [DataMember]
        public string CustomerId { get; set; }

        /// <summary>
        /// Gets or sets the office identifier.
        /// </summary>
        /// <value>
        /// The office identifier.
        /// </value>
        [DataMember]
        public string OfficeId { get; set; }

        /// <summary>
        /// Gets or sets the office code.
        /// </summary>
        /// <value>
        /// The office code.
        /// </value>
        [DataMember]
        public string OfficeCode { get; set; }

        /// <summary>
        /// The office description
        /// </summary>
        [DataMember]
        public string OfficeDescription { get; set; }

        /// <summary>
        /// ExplorerConfigurationId
        /// </summary>
        [DataMember]
        public string ExplorerConfigurationId { get; set; }

        /// <summary>
        /// SubnetMask
        /// </summary>
        [DataMember]
        public string SubnetMask { get; set; }

        /// <summary>
        /// PartialWalkOID
        /// </summary>
        [DataMember]
        public string PartialWalkOID { get; set; }


        /// <summary>
        /// IpStart
        /// </summary>
        [DataMember]
        public string IpStart { get; set; }

        /// <summary>
        /// IpEnd
        /// </summary>
        [DataMember]
        public string IpEnd { get; set; }
    }
}
