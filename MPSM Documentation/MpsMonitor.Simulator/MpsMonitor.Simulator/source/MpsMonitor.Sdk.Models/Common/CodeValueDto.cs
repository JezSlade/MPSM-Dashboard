namespace MpsMonitor.Sdk.Models.Common
{
    public class CodeValueDto<TCode, TValue>
    {
        public CodeValueDto(TCode code, TValue value)
        {
            this.Code = code;
            this.Value = value;
        }

        public CodeValueDto()
        {
        }

        public TCode Code { get; set; }

        public TValue Value { get; set; }
    }
}
