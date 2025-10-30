using System;
using System.Collections.Generic;
using System.ComponentModel;
using System.Text;

namespace MpsMonitor.Sdk.Models.Enums
{
    /// <summary>
    /// 
    /// </summary>
    public enum ManagedStatusEnum
    {
        All = 0,
        Managed = 1,
        NotManaged = 2
    }

    /// <summary>
    /// 
    /// </summary>
    public enum CounterDetailSourceEnum
    {
        Mps = 0,
        HpSds = 1,

    }

    /// <summary>
    /// 
    /// </summary>
    public enum InstalledProductSdsEventTypeEnum
    {
        Undefined = 0,
        Error = 1,
        Warning = 2,
        Info = 3,
        BlackBox = 4
    }

    /// <summary>
    /// 
    /// </summary>
    public enum JamActionServerityEnum : byte
    {
        High = 1,
        Medium = 2,
        Low = 3,
    }

    /// <summary>
    /// 
    /// </summary>
    public enum SdsActionUpdateStateEnum : byte
    {
        [Description("The action has not been resolved")]
        Open = 1,
        [Description("The action is not resolved but will not show as open")]
        Postponed = 2,
        [Description("The action is resolved due to the condition that caused the action to be created no longer exists")]
        ClosedCleared = 3,
        [Description("The action is resolved with no action taken")]
        ClosedIgnored = 4,
        [Description("The action is resolved with the device being repaired and the instructions that were provided were accurate")]
        ClosedFixedAsDesigned = 5,
        [Description("The action is resolved with the device being repaired and the instructions that were provided were not accurate")]
        ClosedIncorrectAction = 6,
        [Description("The customer closed the action in Report A Problem on the printer")]
        ClosedCanceled = 7,
        [Description("Generic closed state")]
        Closed = 8
    }

    /// <summary>
    /// 
    /// </summary>
    public enum InstalledProductJamStatusEnum
    {
        Ok = 0,
        Warning = 1,
        Error = 2
    }

    /// <summary>
    /// 
    /// </summary>
    public enum InstalledProductJamOperationTypeEnum
    {
        FirmwareUpgrade = 1,
        DeviceConfig = 2,
        PowerCycleReset = 3,
        RetrieveDeviceData = 4,
        AdminCredentials = 5,
        SnmpV1V2ReadCredentials = 6,
        SnmpV1V2ReadWriteCredentials = 7,
        SnmpV3Credentials = 8,
        Events = 9,
        Actions = 10,
        InstallRapa = 11,
        UninstallRapa = 12,
        AssessAndRemediate = 13,
        CloneToPolicy = 14
    }

    /// <summary>
    /// 
    /// </summary>
    public enum SupplyAlertManageOptionEnum
    {
        All = 1,
        Managed = 2,
        NotManaged = 3
    }


    /// <summary>
    /// 
    /// </summary>
    public enum SupplyAlertInstallationOptionEnum
    {
        All = 1,
        Installed = 2,
        NotInstalled = 3
    }

    /// <summary>
    /// 
    /// </summary>
    public enum SupplyAlertCancelOptionEnum
    {
        All = 1,
        Canceled = 2,
        NotCanceled = 3
    }

    /// <summary>
    /// 
    /// </summary>
    public enum SupplyAlertHiddenOptionEnum
    {
        All = 1,
        Hidden = 2,
        NotHidden = 3
    }

    /// <summary>
    /// 
    /// </summary>
    public enum SupplyTypeEnum
    {
        MaintenanceKit = 1,
        PhotoConductor = 2,
        Toner = 3,
    }

    /// <summary>
    /// 
    /// </summary>
    public enum ColorTypeEnum
    {
        NotAvailable = 1,
        Black = 2,
        Cyan = 3,
        Magenta = 4,
        Yellow = 5,
        Black1 = 6,
        Black2 = 7,
        Black3 = 8,
    }

    /// <summary>
    /// 
    /// </summary>
    public enum GenerationsEnum
    {
        Manual, //anticipo
        Automatic, //da alert
        ForStock //scorta
    }

    /// <summary>
    /// 
    /// </summary>
    public enum LanguageEnum
    {
        Italiano = 0,
        English = 1,
        Deutsche = 2,
        French = 3,
        Spanish = 4,
        Norwegian = 5,
        Korean = 6,
        Portuguese = 7,
        Catalan = 8,
    }

    /// <summary>
    /// 
    /// </summary>
    public enum ColorEnum
    {
        NotAvailable = 1,
        Mono = 2,
        Colored = 3,
    }


    public enum CommunicationStatusEnum
    {
        All = 0,
        LastDay = 1,
        LastPeriod = 2,
        OverPeriod = 3
    }


    public enum ExplorerDataJamStatusEnum
    {
        Unknown = 0,
        [Description("Connector is okay")]
        OK = 1,
        [Description("Connector has not communicated for over 3 hours")]
        NoRecentCommunication = 2,
        [Description("Connector has not communicated for over 3 days")]
        NeedsAttention = 3,
        [Description("There is another connector installed on the client")]
        WrongRegistration = 4,
        ConnectionNotAvailable = 5,
        NotRegistered = 6,
        Registered = 7,
        InvalidRegistrationKey = 8,
        ConnectorAlreadyRegistered = 9, // Custom ExplorJamStates
        InvalidApiKey = 10,
        JamcNotAvailable = 11,
        Initializing = 12,
        Registering = 13,
        CommunicationError = 14,
        ServiceNotStarted = 15,
        InvalidCertificate = 16,
        CannotFetchCertificateRevocationList = 17,
        ConnectorRegisteredInAnotherCustomer = 18
    }
}

